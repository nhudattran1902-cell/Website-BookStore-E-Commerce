<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Services\MoMoService;
use App\Services\OrderWorkflowService;
use App\Services\VietQRService;
use App\Services\VNPayService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class PaymentGatewayController extends Controller
{
    public function start(Request $request, string $maDonHang, OrderWorkflowService $workflow): RedirectResponse|View
    {
        $order = DonHang::with('thanhToan')
            ->where('ma_don_hang', $maDonHang)
            ->where('id_nguoi_dung', $request->user()->id)
            ->firstOrFail();

        if (! $order->thanhToan || $order->thanhToan->phuong_thuc_thanh_toan === 'COD') {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', 'Đơn hàng này không sử dụng thanh toán trực tuyến.');
        }

        if ($order->trang_thai !== 'cho_xu_ly' || $order->thanhToan->trang_thai === 'da_thanh_toan') {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', 'Đơn hàng hiện không thể thanh toán.');
        }

        if ($order->thanh_toan_het_han_at && $order->thanh_toan_het_han_at->isPast()) {
            $workflow->expirePendingPayment($order->id);

            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', 'Đơn hàng đã hết hạn thanh toán.');
        }

        if (app()->environment(['local', 'testing'])
            && (bool) config('services.payments.demo_enabled')
            && in_array($order->thanhToan->phuong_thuc_thanh_toan, ['MoMo', 'BankTransfer'], true)) {
            return view('customer.payment-demo', [
                'order' => $order,
                'paymentMethod' => $order->thanhToan->phuong_thuc_thanh_toan,
            ]);
        }

        if ($order->thanhToan->phuong_thuc_thanh_toan === 'BankTransfer') {
            try {
                $amount = (int) $order->thanhToan->so_tien;
                $signature = VietQRService::signatureForOrder($order->ma_don_hang, $amount);

                return view('customer.payment-qr', [
                    'order' => $order,
                    'qrUrl' => VietQRService::generateSecureQR($order->ma_don_hang, $amount),
                    'signature' => $signature,
                    'bankId' => config('services.vietqr.bank_id'),
                    'accountNumber' => config('services.vietqr.account_no'),
                    'accountName' => config('services.vietqr.account_name'),
                ]);
            } catch (Throwable $exception) {
                report($exception);

                return redirect()->route('customer.orders.show', $order->id)
                    ->with('error', 'Chuyển khoản VietQR chưa được cấu hình.');
            }
        }

        if ($order->thanhToan->trang_thai === 'that_bai') {
            $order->thanhToan->update(['trang_thai' => 'cho_thanh_toan']);
            $order->update([
                'thanh_toan_het_han_at' => now()->addMinutes((int) config('services.payments.expiry_minutes', 15)),
            ]);
        }

        try {
            $paymentUrl = match ($order->thanhToan->phuong_thuc_thanh_toan) {
                'VNPay' => VNPayService::createPaymentUrl($order->ma_don_hang, (float) $order->thanh_tien),
                'MoMo' => MoMoService::createPaymentUrl($order),
                default => throw new RuntimeException('Phương thức thanh toán không được hỗ trợ.'),
            };

            return redirect()->away($paymentUrl);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', 'Không thể khởi tạo thanh toán. Kiểm tra cấu hình cổng thanh toán rồi thử lại.');
        }
    }

    public function vnpayIpn(Request $request, OrderWorkflowService $workflow): JsonResponse
    {
        $input = $request->all();

        if (! VNPayService::verifyResponse($input)
            || ($input['vnp_TmnCode'] ?? null) !== config('services.vnpay.tmn_code')) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid Signature']);
        }

        $orderCode = $input['vnp_TxnRef'] ?? null;
        $transactionId = $input['vnp_TransactionNo'] ?? null;
        $amountMinor = filter_var($input['vnp_Amount'] ?? null, FILTER_VALIDATE_INT);

        if (! is_string($orderCode) || ! is_string($transactionId) || $amountMinor === false || $amountMinor % 100 !== 0) {
            return response()->json(['RspCode' => '04', 'Message' => 'Invalid transaction data']);
        }

        if (($input['vnp_ResponseCode'] ?? null) !== '00' || ($input['vnp_TransactionStatus'] ?? null) !== '00') {
            $workflow->markPaymentFailed($orderCode, 'vnpay', 'vnpay-'.$transactionId);

            return response()->json(['RspCode' => '00', 'Message' => 'Payment result received']);
        }

        $result = $workflow->confirmPayment($orderCode, (int) ($amountMinor / 100), $transactionId, 'vnpay');

        return match ($result) {
            'paid', 'already_paid' => response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']),
            'not_found' => response()->json(['RspCode' => '01', 'Message' => 'Order not found']),
            'amount_mismatch' => response()->json(['RspCode' => '04', 'Message' => 'Invalid amount']),
            default => response()->json(['RspCode' => '02', 'Message' => 'Order already processed']),
        };
    }

    public function momoIpn(Request $request, OrderWorkflowService $workflow): JsonResponse
    {
        $input = $request->all();

        if (! MoMoService::verifyIpn($input)
            || ($input['partnerCode'] ?? null) !== config('services.momo.partner_code')) {
            return response()->json(['resultCode' => 1, 'message' => 'Invalid signature'], 401);
        }

        $orderCode = $input['orderId'] ?? null;
        $transactionId = isset($input['transId']) ? (string) $input['transId'] : null;
        $amount = filter_var($input['amount'] ?? null, FILTER_VALIDATE_INT);
        $requestId = isset($input['requestId']) ? (string) $input['requestId'] : null;

        if (! is_string($orderCode) || ! $transactionId || $amount === false || ! $requestId) {
            return response()->json(['resultCode' => 1, 'message' => 'Invalid transaction data'], 400);
        }

        if ((int) ($input['resultCode'] ?? -1) !== 0) {
            $workflow->markPaymentFailed($orderCode, 'momo', 'momo-'.$requestId);

            return response()->json(['resultCode' => 0, 'message' => 'Payment result received']);
        }

        $result = $workflow->confirmPayment($orderCode, $amount, $transactionId, 'momo');

        return match ($result) {
            'paid', 'already_paid' => response()->json(['resultCode' => 0, 'message' => 'Confirmed']),
            'not_found' => response()->json(['resultCode' => 1, 'message' => 'Order not found'], 404),
            'amount_mismatch' => response()->json(['resultCode' => 1, 'message' => 'Invalid amount'], 400),
            default => response()->json(['resultCode' => 1, 'message' => 'Order already processed'], 409),
        };
    }
}
