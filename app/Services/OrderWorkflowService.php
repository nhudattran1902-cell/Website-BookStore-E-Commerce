<?php

namespace App\Services;

use App\Models\DonHang;
use App\Models\KhoHang;
use App\Models\LichSuDonHang;
use App\Models\ThanhToan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class OrderWorkflowService
{
    public function reserveInventory(DonHang $order): void
    {
        $items = $order->chiTietDonHang()->orderBy('id_sach')->get();
        $stocks = $this->lockedStocks($items);

        foreach ($items->groupBy('id_sach') as $bookId => $bookItems) {
            $quantity = (int) $bookItems->sum('so_luong');
            $stock = $stocks->get($bookId);
            $available = $stock ? $stock->so_luong_ton - $stock->so_luong_dat_truoc : 0;

            if (! $stock || $available < $quantity) {
                throw ValidationException::withMessages([
                    'stock' => 'Một hoặc nhiều sách vừa hết hàng. Vui lòng cập nhật giỏ hàng rồi thử lại.',
                ]);
            }
        }

        foreach ($items->groupBy('id_sach') as $bookId => $bookItems) {
            $stocks->get($bookId)->increment('so_luong_dat_truoc', (int) $bookItems->sum('so_luong'));
        }

        $order->update(['da_giu_ton' => true]);
    }

    public function transition(
        int $orderId,
        string $newStatus,
        ?int $changedBy,
        ?string $note = null,
        ?string $source = null,
        ?string $eventId = null,
        ?string $location = null,
    ): bool {
        $transitionResult = DB::transaction(function () use ($orderId, $newStatus, $changedBy, $note, $source, $eventId, $location): array {
            $order = DonHang::with('chiTietDonHang')
                ->lockForUpdate()
                ->findOrFail($orderId);

            if ($this->isDuplicateEvent($source, $eventId)) {
                return ['transitioned' => false, 'restocked_book_ids' => []];
            }

            $currentStatus = $order->trang_thai;
            $validTransitions = [
                'cho_xu_ly' => ['dang_xu_ly', 'da_huy'],
                'dang_xu_ly' => ['dang_giao', 'da_huy'],
                'dang_giao' => ['hoan_thanh'],
                'hoan_thanh' => [],
                'da_huy' => [],
            ];

            if (! in_array($newStatus, $validTransitions[$currentStatus] ?? [], true)) {
                throw new LogicException("Không thể chuyển đơn từ {$currentStatus} sang {$newStatus}.");
            }

            if ($currentStatus === 'cho_xu_ly' && $newStatus === 'dang_xu_ly') {
                $order->loadMissing('thanhToan');
                $payment = $order->thanhToan;

                if ($payment
                    && $payment->phuong_thuc_thanh_toan !== 'COD'
                    && $payment->trang_thai !== 'da_thanh_toan') {
                    throw new LogicException('Chỉ được xử lý đơn trực tuyến sau khi đã xác nhận thanh toán.');
                }
            }

            if ($newStatus === 'da_huy') {
                $order->loadMissing('thanhToan');

                if ($order->thanhToan?->trang_thai === 'da_thanh_toan') {
                    throw new LogicException('Đơn đã thanh toán cần được hoàn tiền qua cổng thanh toán trước khi hủy.');
                }
            }

            $items = $order->chiTietDonHang->sortBy('id_sach');
            $stocks = $this->lockedStocks($items);
            $restockedBookIds = [];

            if ($currentStatus === 'cho_xu_ly' && $newStatus === 'dang_xu_ly' && $order->da_giu_ton) {
                foreach ($items->groupBy('id_sach') as $bookId => $bookItems) {
                    $quantity = (int) $bookItems->sum('so_luong');
                    $stock = $stocks->get($bookId);

                    if (! $stock || $stock->so_luong_dat_truoc < $quantity || $stock->so_luong_ton < $quantity) {
                        throw new LogicException('Số lượng giữ chỗ không khớp với chi tiết đơn hàng.');
                    }

                    $stock->decrement('so_luong_dat_truoc', $quantity);
                    $stock->decrement('so_luong_ton', $quantity);
                }
            }

            if ($newStatus === 'da_huy') {
                foreach ($items->groupBy('id_sach') as $bookId => $bookItems) {
                    $quantity = (int) $bookItems->sum('so_luong');
                    $stock = $stocks->get($bookId);

                    if (! $stock) {
                        continue;
                    }

                    $restockedBookIds[] = (int) $bookId;

                    if ($currentStatus === 'cho_xu_ly' && $order->da_giu_ton) {
                        if ($stock->so_luong_dat_truoc < $quantity) {
                            throw new LogicException('Số lượng giữ chỗ không khớp với chi tiết đơn hàng.');
                        }

                        $stock->decrement('so_luong_dat_truoc', $quantity);
                    } else {
                        $stock->increment('so_luong_ton', $quantity);
                    }
                }
            }

            $order->update(['trang_thai' => $newStatus]);

            if ($newStatus === 'hoan_thanh') {
                $order->loadMissing('thanhToan');

                if (
                    $order->thanhToan?->phuong_thuc_thanh_toan === 'COD'
                    && $order->thanhToan->trang_thai === 'cho_thanh_toan'
                ) {
                    $order->thanhToan->update([
                        'trang_thai' => 'da_thanh_toan',
                        'ngay_thanh_toan' => now(),
                    ]);
                }
            }

            if ($newStatus === 'da_huy' && $order->thanhToan?->trang_thai === 'cho_thanh_toan') {
                $order->thanhToan->update([
                    'trang_thai' => $order->thanhToan->phuong_thuc_thanh_toan === 'COD' ? 'da_huy' : 'that_bai',
                ]);
            }

            $this->writeLog($order, $currentStatus, $newStatus, $changedBy, $note, $source, $eventId, $location);

            return [
                'transitioned' => true,
                'restocked_book_ids' => array_values(array_unique($restockedBookIds)),
            ];
        }, attempts: 3);

        foreach ($transitionResult['restocked_book_ids'] as $bookId) {
            app(StockAvailabilityNotifier::class)->notifyIfAvailable($bookId);
        }

        return $transitionResult['transitioned'];
    }

    public function confirmPayment(string $orderCode, int $amount, string $transactionId, string $source): string
    {
        return DB::transaction(function () use ($orderCode, $amount, $transactionId, $source): string {
            $order = DonHang::with('chiTietDonHang')
                ->where('ma_don_hang', $orderCode)
                ->lockForUpdate()
                ->first();
            $payment = $order
                ? ThanhToan::where('id_don_hang', $order->id)->lockForUpdate()->first()
                : null;

            if (! $order || ! $payment) {
                return 'not_found';
            }

            /** @var DonHang $order */
            if ((int) $payment->so_tien !== $amount) {
                return 'amount_mismatch';
            }

            if ($payment->trang_thai === 'da_thanh_toan') {
                return $payment->ma_giao_dich === $transactionId ? 'already_paid' : 'transaction_conflict';
            }

            if ($payment->trang_thai !== 'cho_thanh_toan' || $order->trang_thai === 'da_huy') {
                return 'not_payable';
            }

            $expectedMethod = match ($source) {
                'vnpay' => 'VNPay',
                'momo' => 'MoMo',
                'bank_transfer' => 'BankTransfer',
                default => null,
            };

            if ($expectedMethod === null || $payment->phuong_thuc_thanh_toan !== $expectedMethod) {
                return 'provider_mismatch';
            }

            if ($order->thanh_toan_het_han_at && $order->thanh_toan_het_han_at->isPast()) {
                $payment->update(['trang_thai' => 'that_bai']);
                $this->transition(
                    $order->id,
                    'da_huy',
                    null,
                    'Đơn tự hủy do quá hạn thanh toán; tồn kho đã được giải phóng.',
                    'payment_expiry',
                    'payment-expired-'.$order->id,
                );

                return 'expired';
            }

            $payment->update([
                'trang_thai' => 'da_thanh_toan',
                'ma_giao_dich' => $transactionId,
                'ngay_thanh_toan' => now(),
            ]);

            if ($order->trang_thai === 'cho_xu_ly') {
                $this->transition(
                    $order->id,
                    'dang_xu_ly',
                    null,
                    'Thanh toán được xác nhận',
                    $source,
                    $transactionId,
                );
            } else {
                $this->recordCarrierEvent(
                    $order,
                    $source,
                    $transactionId,
                    'Thanh toán được xác nhận',
                );
            }

            return 'paid';
        }, attempts: 3);
    }

    public function markPaymentFailed(string $orderCode, string $source, string $eventId): bool
    {
        return DB::transaction(function () use ($orderCode, $source, $eventId): bool {
            $order = DonHang::where('ma_don_hang', $orderCode)->lockForUpdate()->first();
            $payment = $order
                ? ThanhToan::where('id_don_hang', $order->id)->lockForUpdate()->first()
                : null;

            if (! $order || ! $payment || $payment->trang_thai !== 'cho_thanh_toan') {
                return false;
            }

            /** @var DonHang $order */
            $expectedMethod = match ($source) {
                'vnpay' => 'VNPay',
                'momo' => 'MoMo',
                default => null,
            };

            if ($payment->phuong_thuc_thanh_toan !== $expectedMethod) {
                return false;
            }

            $payment->update(['trang_thai' => 'that_bai']);
            $this->recordCarrierEvent($order, $source, $eventId, 'Giao dịch thanh toán thất bại.');

            return true;
        }, attempts: 3);
    }

    public function expirePendingPayment(int $orderId): bool
    {
        return DB::transaction(function () use ($orderId): bool {
            $order = DonHang::with('chiTietDonHang')->lockForUpdate()->find($orderId);
            $payment = $order
                ? ThanhToan::where('id_don_hang', $order->id)->lockForUpdate()->first()
                : null;

            if (! $order
                || ! $payment
                || $order->trang_thai !== 'cho_xu_ly'
                || ! in_array($payment->trang_thai, ['cho_thanh_toan', 'that_bai'], true)
                || ! $order->thanh_toan_het_han_at
                || $order->thanh_toan_het_han_at->isFuture()) {
                return false;
            }

            $payment->update(['trang_thai' => 'that_bai']);

            return $this->transition(
                $order->id,
                'da_huy',
                null,
                'Đơn tự hủy do quá hạn thanh toán; tồn kho đã được giải phóng.',
                'payment_expiry',
                'payment-expired-'.$order->id,
            );
        }, attempts: 3);
    }

    public function recordInitial(DonHang $order, ?int $changedBy = null): void
    {
        $this->writeLog($order, null, $order->trang_thai, $changedBy, 'Đơn hàng được tạo', 'system');
    }

    public function recordCarrierEvent(
        DonHang $order,
        string $source,
        string $eventId,
        string $note,
        ?string $location = null,
        ?string $newStatus = null,
    ): bool {
        if ($newStatus !== null && $newStatus !== $order->trang_thai) {
            return $this->transition($order->id, $newStatus, null, $note, $source, $eventId, $location);
        }

        return DB::transaction(function () use ($order, $source, $eventId, $note, $location): bool {
            $lockedOrder = DonHang::lockForUpdate()->findOrFail($order->id);

            if ($this->isDuplicateEvent($source, $eventId)) {
                return false;
            }

            $this->writeLog(
                $lockedOrder,
                $lockedOrder->trang_thai,
                $lockedOrder->trang_thai,
                null,
                $note,
                $source,
                $eventId,
                $location,
            );

            return true;
        }, attempts: 3);
    }

    private function lockedStocks(Collection $items): Collection
    {
        $bookIds = $items->pluck('id_sach')->unique()->sort()->values();

        if ($bookIds->isEmpty()) {
            return collect();
        }

        return KhoHang::whereIn('id_sach', $bookIds)
            ->orderBy('id_sach')
            ->lockForUpdate()
            ->get()
            ->keyBy('id_sach');
    }

    private function isDuplicateEvent(?string $source, ?string $eventId): bool
    {
        return $source !== null && $eventId !== null
            && LichSuDonHang::where('nguon', $source)->where('ma_su_kien', $eventId)->exists();
    }

    private function writeLog(
        DonHang $order,
        ?string $oldStatus,
        string $newStatus,
        ?int $changedBy,
        ?string $note,
        ?string $source,
        ?string $eventId = null,
        ?string $location = null,
    ): void {
        LichSuDonHang::create([
            'id_don_hang' => $order->id,
            'trang_thai_cu' => $oldStatus,
            'trang_thai_moi' => $newStatus,
            'id_nguoi_thay_doi' => $changedBy,
            'ghi_chu' => $note,
            'nguon' => $source,
            'ma_su_kien' => $eventId,
            'vi_tri' => $location,
        ]);
    }
}
