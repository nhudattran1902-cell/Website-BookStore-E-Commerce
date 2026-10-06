<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ChiTietDonHang;
use App\Models\DonHang;
use App\Models\GioHang;
use App\Models\ThanhToan;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    // 1. Hiển thị trang Form thanh toán
    public function index()
    {
        $userId = Auth::id();

        $gioHang = GioHang::with(['chiTietGioHang.sach'])
            ->where('id_nguoi_dung', $userId)
            ->first();

        if (! $gioHang || $gioHang->chiTietGioHang->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống!');
        }

        $cartItems = $gioHang->chiTietGioHang;
        $tongTien = $cartItems->sum(function ($item) {
            $gia = $item->sach->gia_khuyen_mai ?? $item->sach->gia_ban;

            return $gia * $item->so_luong;
        });
        $total = $tongTien;

        return view('customer.checkout', compact('cartItems', 'tongTien', 'total'));
    }

    // 2. Xử lý Đặt hàng (Lưu thông tin vào CSDL)
    public function process(Request $request, OrderWorkflowService $workflow)
    {
        // Đồng bộ tên field validate khớp với checkout.blade.php
        $validated = $request->validate([
            'ten_nguoi_nhan' => 'required|string|max:255',
            'sdt_nguoi_nhan' => 'required|string|max:20',
            'dia_chi_giao_hang' => 'required|string',
            'phuong_thuc_thanh_toan' => 'required|in:COD,MoMo,VNPay,BankTransfer',
            'ghi_chu' => 'nullable|string',
        ]);

        $paymentMethod = $validated['phuong_thuc_thanh_toan'];
        $demoEnabled = app()->environment(['local', 'testing']) && (bool) config('services.payments.demo_enabled');
        $paymentIsConfigured = match ($paymentMethod) {
            'VNPay' => filled(config('services.vnpay.base_url'))
                && filled(config('services.vnpay.tmn_code'))
                && filled(config('services.vnpay.hash_secret')),
            'MoMo' => $demoEnabled || (filled(config('services.momo.endpoint'))
                && filled(config('services.momo.partner_code'))
                && filled(config('services.momo.access_key'))
                && filled(config('services.momo.secret_key'))),
            'BankTransfer' => $demoEnabled || (filled(config('services.vietqr.bank_id'))
                && filled(config('services.vietqr.account_no'))
                && filled(config('services.vietqr.account_name'))
                && filled(config('services.vietqr.signature_key'))),
            default => true,
        };

        if (! $paymentIsConfigured) {
            $configurationMessage = match ($paymentMethod) {
                'VNPay' => 'VNPay chưa được cấu hình. Vui lòng thiết lập VNPAY_TMN_CODE và VNPAY_HASH_SECRET trong .env.',
                'MoMo' => 'MoMo chưa được cấu hình. Vui lòng thiết lập MOMO_PARTNER_CODE, MOMO_ACCESS_KEY và MOMO_SECRET_KEY trong .env.',
                'BankTransfer' => 'VietQR chưa được cấu hình. Vui lòng thiết lập thông tin ngân hàng và VIETQR_SIGNATURE_KEY trong .env.',
                default => 'Phương thức thanh toán chưa được cấu hình.',
            };

            throw ValidationException::withMessages([
                'phuong_thuc_thanh_toan' => $configurationMessage,
            ]);
        }

        $userId = Auth::id();
        $donHang = DB::transaction(function () use ($validated, $paymentMethod, $userId, $workflow): DonHang {
            /** @var GioHang|null $gioHang */
            $gioHang = GioHang::with(['chiTietGioHang.sach'])
                ->where('id_nguoi_dung', $userId)
                ->lockForUpdate()
                ->first();

            if (! $gioHang || $gioHang->chiTietGioHang->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng của bạn đang trống!']);
            }

            foreach ($gioHang->chiTietGioHang as $item) {
                if (! $item->sach || ! $item->sach->dang_hoat_dong) {
                    throw ValidationException::withMessages([
                        'cart' => 'Một hoặc nhiều sách trong giỏ hiện không còn được bán.',
                    ]);
                }
            }

            $tongTien = $gioHang->chiTietGioHang->sum(function ($item): float {
                $gia = $item->sach->gia_khuyen_mai ?? $item->sach->gia_ban;

                return $gia * $item->so_luong;
            });
            $isOnlinePayment = $paymentMethod !== 'COD';

            $donHang = DonHang::create([
                'ma_don_hang' => 'ORD-'.strtoupper(Str::random(8)),
                'id_nguoi_dung' => $userId,
                'ten_nguoi_nhan' => $validated['ten_nguoi_nhan'],
                'sdt_nguoi_nhan' => $validated['sdt_nguoi_nhan'],
                'dia_chi_nhan' => $validated['dia_chi_giao_hang'],
                'dia_chi_giao_hang' => $validated['dia_chi_giao_hang'],
                'tong_tien' => $tongTien,
                'so_tien_giam_gia' => 0,
                'thanh_tien' => $tongTien,
                'trang_thai' => 'cho_xu_ly',
                'da_giu_ton' => false,
                'thanh_toan_het_han_at' => $isOnlinePayment
                    ? now()->addMinutes((int) config('services.payments.expiry_minutes', 15))
                    : null,
                'ghi_chu' => $validated['ghi_chu'] ?? null,
            ]);

            foreach ($gioHang->chiTietGioHang as $item) {
                $donGia = $item->sach->gia_khuyen_mai ?? $item->sach->gia_ban;

                ChiTietDonHang::create([
                    'id_don_hang' => $donHang->id,
                    'id_sach' => $item->id_sach,
                    'don_gia' => $donGia,
                    'so_luong' => $item->so_luong,
                    'thanh_tien' => $donGia * $item->so_luong,
                ]);
            }

            $workflow->reserveInventory($donHang);

            ThanhToan::create([
                'id_don_hang' => $donHang->id,
                'phuong_thuc_thanh_toan' => $paymentMethod,
                'so_tien' => $tongTien,
                'trang_thai' => 'cho_thanh_toan',
            ]);

            $workflow->recordInitial($donHang, $userId);
            $gioHang->chiTietGioHang()->delete();

            return $donHang;
        }, attempts: 3);

        if ($paymentMethod !== 'COD') {
            return redirect()->route('checkout.payment.start', $donHang->ma_don_hang);
        }

        return redirect()->route('checkout.success', $donHang->ma_don_hang);
    }

    // 3. Trang đặt hàng thành công
    public function success($maDonHang)
    {
        // Kiểm tra đơn hàng thuộc về user hiện tại kèm theo thông tin chi tiết & thanh toán
        $donHang = DonHang::with(['chiTietDonHang.sach', 'thanhToan', 'nguoiDung'])
            ->where('ma_don_hang', $maDonHang)
            ->where('id_nguoi_dung', Auth::id())
            ->firstOrFail();

        // Gán alias $order bằng $donHang để khớp với view checkout-success.blade.php
        $order = $donHang;

        return view('customer.checkout-success', compact('donHang', 'order'));
    }
}
