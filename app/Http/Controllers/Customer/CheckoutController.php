<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ChiTietDonHang;
use App\Models\DonHang;
use App\Models\GioHang;
use App\Models\MaGiamGia;
use App\Models\ThanhToan;
use App\Services\AddressLocationService;
use App\Services\DiscountService;
use App\Services\OrderWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    // 1. Hiển thị trang Form thanh toán
    public function index(DiscountService $discountService, AddressLocationService $locations): View|RedirectResponse
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
        $discountAmount = 0;
        $discountCode = session('checkout.discount_code');
        $discountError = null;

        if (is_string($discountCode) && $discountCode !== '') {
            try {
                $discountAmount = $discountService->evaluate($discountCode, (int) $tongTien, (int) $userId)['discount'];
            } catch (ValidationException $exception) {
                $discountError = $exception->errors()['discount_code'][0] ?? 'Mã giảm giá không còn hợp lệ.';
                session()->forget('checkout.discount_code');
                $discountCode = null;
            }
        }

        $total = max(0, (int) $tongTien - $discountAmount);
        $availableVouchers = $discountService->availableForCustomer((int) $userId, (int) $tongTien);

        $provinces = $locations->provinces();

        return view('customer.checkout', compact('cartItems', 'tongTien', 'total', 'discountAmount', 'discountCode', 'discountError', 'availableVouchers', 'provinces'));
    }

    public function applyDiscount(Request $request, DiscountService $discountService): RedirectResponse
    {
        $validated = $request->validate([
            'ma_code' => ['required', 'string', 'max:50'],
        ]);
        $subtotal = $this->currentCartSubtotal();

        if ($subtotal <= 0) {
            throw ValidationException::withMessages(['discount_code' => 'Giỏ hàng đang trống.']);
        }

        $discountService->evaluate($validated['ma_code'], $subtotal, (int) Auth::id());
        $request->session()->put('checkout.discount_code', Str::upper(trim($validated['ma_code'])));

        return redirect()->route('checkout.index')->with('success', 'Đã áp dụng mã giảm giá.');
    }

    public function removeDiscount(Request $request): RedirectResponse
    {
        $request->session()->forget('checkout.discount_code');

        return redirect()->route('checkout.index')->with('success', 'Đã gỡ mã giảm giá.');
    }

    // 2. Xử lý Đặt hàng (Lưu thông tin vào CSDL)
    public function process(Request $request, OrderWorkflowService $workflow, DiscountService $discountService, AddressLocationService $locations): RedirectResponse
    {
        // Đồng bộ tên field validate khớp với checkout.blade.php
        $validated = $request->validate([
            'ten_nguoi_nhan' => 'required|string|max:255',
            'sdt_nguoi_nhan' => 'required|string|max:20',
            'so_nha_duong' => ['required', 'string', 'max:300'],
            'province_code' => ['required', 'string', 'size:2'],
            'ward_code' => ['required', 'string', 'size:5'],
            'phuong_thuc_thanh_toan' => 'required|in:COD,MoMo,VNPay,BankTransfer',
            'ghi_chu' => 'nullable|string',
        ]);

        $shippingAddress = $locations->formatAddress(
            $validated['so_nha_duong'],
            $validated['province_code'],
            $validated['ward_code'],
        );

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
        $discountCode = $request->session()->get('checkout.discount_code');
        $donHang = DB::transaction(function () use ($validated, $shippingAddress, $paymentMethod, $userId, $workflow, $discountService, $discountCode): DonHang {
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
            $coupon = null;
            $discountAmount = 0;

            if (is_string($discountCode) && $discountCode !== '') {
                $discountResult = $discountService->evaluate($discountCode, (int) $tongTien, (int) $userId, lockForUpdate: true);
                /** @var MaGiamGia $coupon */
                $coupon = $discountResult['coupon'];
                $discountAmount = $discountResult['discount'];
                $discountService->recordUsage($coupon);
            }

            $totalAfterDiscount = max(0, (int) $tongTien - $discountAmount);
            $isOnlinePayment = $paymentMethod !== 'COD';

            $donHang = DonHang::create([
                'ma_don_hang' => 'ORD-'.strtoupper(Str::random(8)),
                'id_nguoi_dung' => $userId,
                'id_ma_giam_gia' => $coupon?->id,
                'ten_nguoi_nhan' => $validated['ten_nguoi_nhan'],
                'sdt_nguoi_nhan' => $validated['sdt_nguoi_nhan'],
                'dia_chi_nhan' => $shippingAddress,
                'dia_chi_giao_hang' => $shippingAddress,
                'tong_tien' => $tongTien,
                'so_tien_giam_gia' => $discountAmount,
                'thanh_tien' => $totalAfterDiscount,
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
                'so_tien' => $totalAfterDiscount,
                'trang_thai' => 'cho_thanh_toan',
            ]);

            $workflow->recordInitial($donHang, $userId);
            $gioHang->chiTietGioHang()->delete();

            return $donHang;
        }, attempts: 3);

        $request->session()->forget('checkout.discount_code');

        if ($paymentMethod !== 'COD') {
            return redirect()->route('checkout.payment.start', $donHang->ma_don_hang);
        }

        return redirect()->route('checkout.success', $donHang->ma_don_hang);
    }

    // 3. Trang đặt hàng thành công
    public function success(string $maDonHang): View
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

    private function currentCartSubtotal(): int
    {
        $cart = GioHang::with('chiTietGioHang.sach')
            ->where('id_nguoi_dung', Auth::id())
            ->first();

        if (! $cart) {
            return 0;
        }

        return (int) $cart->chiTietGioHang->sum(function ($item): float {
            $price = $item->sach?->gia_khuyen_mai ?? $item->sach?->gia_ban ?? 0;

            return $price * $item->so_luong;
        });
    }
}
