<?php

namespace App\Services;

use App\Models\DonHang;
use App\Models\MaGiamGia;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DiscountService
{
    /**
     * @return array{coupon: MaGiamGia, discount: int}
     */
    public function evaluate(string $code, int $subtotal, ?int $customerId = null, bool $lockForUpdate = false): array
    {
        $couponQuery = MaGiamGia::query()
            ->whereRaw('UPPER(ma_code) = ?', [Str::upper(trim($code))]);

        if ($lockForUpdate) {
            $couponQuery->lockForUpdate();
        }

        $coupon = $couponQuery->first();

        if (! $coupon || ! $coupon->dang_hoat_dong) {
            $this->reject('Mã giảm giá không tồn tại hoặc đã ngừng hoạt động.');
        }

        if ($coupon->ngay_bat_dau?->isFuture()) {
            $this->reject('Mã giảm giá chưa đến thời gian sử dụng.');
        }

        if ($coupon->ngay_het_han?->isPast()) {
            $this->reject('Mã giảm giá đã hết hạn.');
        }

        if ($coupon->gioi_han_luot !== null && $coupon->da_su_dung >= $coupon->gioi_han_luot) {
            $this->reject('Mã giảm giá đã hết lượt sử dụng.');
        }

        if ($customerId !== null && DonHang::query()
            ->where('id_nguoi_dung', $customerId)
            ->where('id_ma_giam_gia', $coupon->id)
            ->exists()) {
            $this->reject('Bạn đã sử dụng mã giảm giá này trước đó. Mỗi khách chỉ được dùng một lần.');
        }

        if ($subtotal < $coupon->don_toi_thieu) {
            $this->reject('Đơn hàng chưa đạt giá trị tối thiểu để dùng mã này.');
        }

        $discount = $this->calculateDiscount($coupon, $subtotal);

        if ($discount <= 0) {
            $this->reject('Mã giảm giá không tạo ra khoản giảm hợp lệ cho đơn hàng này.');
        }

        return [
            'coupon' => $coupon,
            'discount' => $discount,
        ];
    }

    /**
     * @return Collection<int, MaGiamGia>
     */
    public function availableForCustomer(int $customerId, int $subtotal): Collection
    {
        $now = now();
        $usedCouponIds = DonHang::query()
            ->where('id_nguoi_dung', $customerId)
            ->whereNotNull('id_ma_giam_gia')
            ->pluck('id_ma_giam_gia');

        $query = MaGiamGia::query()
            ->where('dang_hoat_dong', true)
            ->where(fn ($query) => $query->whereNull('ngay_bat_dau')->orWhere('ngay_bat_dau', '<=', $now))
            ->where(fn ($query) => $query->whereNull('ngay_het_han')->orWhere('ngay_het_han', '>=', $now))
            ->where(fn ($query) => $query->whereNull('gioi_han_luot')->orWhereColumn('da_su_dung', '<', 'gioi_han_luot'));

        if ($usedCouponIds->isNotEmpty()) {
            $query->whereNotIn('id', $usedCouponIds);
        }

        return $query->orderBy('ma_code')
            ->get()
            ->filter(fn (MaGiamGia $coupon): bool => $subtotal >= $coupon->don_toi_thieu
                && $this->calculateDiscount($coupon, $subtotal) > 0)
            ->map(function (MaGiamGia $coupon) use ($subtotal): MaGiamGia {
                $coupon->setAttribute('discount_preview', $this->calculateDiscount($coupon, $subtotal));

                return $coupon;
            })
            ->values();
    }

    public function recordUsage(MaGiamGia $coupon): void
    {
        DB::table('ma_giam_gia')
            ->where('id', $coupon->id)
            ->increment('da_su_dung');
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['discount_code' => $message]);
    }

    private function calculateDiscount(MaGiamGia $coupon, int $subtotal): int
    {
        $discount = $coupon->loai_giam === 'phan_tram'
            ? (int) round($subtotal * $coupon->gia_tri / 100)
            : $coupon->gia_tri;

        if ($coupon->loai_giam === 'phan_tram' && $coupon->gia_tri_toi_da !== null) {
            $discount = min($discount, $coupon->gia_tri_toi_da);
        }

        return min($discount, $subtotal);
    }
}
