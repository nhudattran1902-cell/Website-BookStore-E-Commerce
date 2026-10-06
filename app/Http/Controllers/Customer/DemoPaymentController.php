<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\NguoiDung;
use App\Notifications\PaymentReceivedNotification;
use App\Services\OrderWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DemoPaymentController extends Controller
{
    public function __invoke(Request $request, string $maDonHang, OrderWorkflowService $workflow): RedirectResponse
    {
        abort_unless(
            app()->environment(['local', 'testing']) && (bool) config('services.payments.demo_enabled'),
            404,
        );

        $order = DonHang::with('thanhToan')
            ->where('ma_don_hang', $maDonHang)
            ->where('id_nguoi_dung', $request->user()->id)
            ->firstOrFail();

        $payment = $order->thanhToan;
        $source = match ($payment?->phuong_thuc_thanh_toan) {
            'MoMo' => 'momo',
            'BankTransfer' => 'bank_transfer',
            default => abort(404),
        };
        $transactionId = 'DEMO-'.Str::uuid();
        $result = $workflow->confirmPayment(
            $order->ma_don_hang,
            (int) $payment->so_tien,
            $transactionId,
            $source,
        );

        if ($result === 'already_paid') {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('info', 'Đơn hàng này đã được thanh toán trước đó.');
        }

        if ($result !== 'paid') {
            return redirect()->route('customer.orders.show', $order->id)
                ->with('error', 'Không thể mô phỏng thanh toán cho đơn hàng này.');
        }

        $notify = fn (): PaymentReceivedNotification => new PaymentReceivedNotification(
            $order->id,
            $order->ma_don_hang,
            $payment->phuong_thuc_thanh_toan,
            (int) $payment->so_tien,
        );

        $order->nguoiDung->notify($notify());

        NguoiDung::query()
            ->whereHas('vaiTro', fn ($query) => $query->where('ten_vai_tro', 'admin'))
            ->each(fn (NguoiDung $admin) => $admin->notify($notify()));

        return redirect()->route('customer.orders.show', $order->id)
            ->with('success', 'Thanh toán demo thành công. Đã gửi thông báo cho bạn và quản trị viên.');
    }
}
