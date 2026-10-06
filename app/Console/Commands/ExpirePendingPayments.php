<?php

namespace App\Console\Commands;

use App\Models\DonHang;
use App\Services\OrderWorkflowService;
use Illuminate\Console\Command;

class ExpirePendingPayments extends Command
{
    protected $signature = 'orders:expire-pending-payments';

    protected $description = 'Cancel expired unpaid orders and release reserved stock';

    public function handle(OrderWorkflowService $workflow): int
    {
        $orderIds = DonHang::query()
            ->where('trang_thai', 'cho_xu_ly')
            ->whereNotNull('thanh_toan_het_han_at')
            ->where('thanh_toan_het_han_at', '<=', now())
            ->whereHas('thanhToan', fn ($query) => $query->whereIn('trang_thai', ['cho_thanh_toan', 'that_bai']))
            ->orderBy('id')
            ->limit(100)
            ->pluck('id');

        $expiredCount = 0;
        foreach ($orderIds as $orderId) {
            if ($workflow->expirePendingPayment((int) $orderId)) {
                $expiredCount++;
            }
        }

        $this->info("Đã hết hạn {$expiredCount} đơn hàng chưa thanh toán.");

        return self::SUCCESS;
    }
}
