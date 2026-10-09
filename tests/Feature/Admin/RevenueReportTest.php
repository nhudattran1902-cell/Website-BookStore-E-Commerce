<?php

namespace Tests\Feature\Admin;

use App\Models\DonHang;
use App\Models\NguoiDung;
use App\Models\ThanhToan;
use App\Models\VaiTro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RevenueReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_report_counts_only_successful_payments_by_payment_date(): void
    {
        $admin = $this->createAdmin();
        $this->createPayment($admin, '2026-01-08 09:00:00', 100000, 'da_thanh_toan');
        $this->createPayment($admin, '2026-01-08 14:30:00', 250000, 'da_thanh_toan');
        $this->createPayment($admin, '2026-01-08 15:00:00', 900000, 'cho_thanh_toan');
        $this->createPayment($admin, '2026-01-08 16:00:00', 700000, 'hoan_tien');
        $this->createPayment($admin, '2026-02-02 10:00:00', 500000, 'da_thanh_toan');

        $this->actingAsAdminWithTwoFactor($admin)
            ->get(route('admin.reports.revenue', ['period' => 'day', 'year' => 2026, 'month' => 1]))
            ->assertOk()
            ->assertViewHas('totalRevenue', 350000)
            ->assertViewHas('totalPayments', 2)
            ->assertViewHas('revenueByPeriod', fn ($rows): bool => (int) $rows->firstWhere('period_key', '2026-01-08')?->revenue === 350000)
            ->assertSee('Doanh thu từng ngày trong tháng 1/2026');
    }

    public function test_monthly_report_groups_successful_payments_for_selected_year(): void
    {
        $admin = $this->createAdmin();
        $this->createPayment($admin, '2026-01-08 09:00:00', 100000, 'da_thanh_toan');
        $this->createPayment($admin, '2026-01-20 09:00:00', 250000, 'da_thanh_toan');
        $this->createPayment($admin, '2026-02-02 09:00:00', 500000, 'da_thanh_toan');
        $this->createPayment($admin, '2025-12-28 09:00:00', 800000, 'da_thanh_toan');

        $this->actingAsAdminWithTwoFactor($admin)
            ->get(route('admin.reports.revenue', ['period' => 'month', 'year' => 2026]))
            ->assertOk()
            ->assertViewHas('totalRevenue', 850000)
            ->assertViewHas('totalPayments', 3)
            ->assertViewHas('revenueByPeriod', fn ($rows): bool => (int) $rows->firstWhere('period_key', '2026-01')?->revenue === 350000
                && (int) $rows->firstWhere('period_key', '2026-02')?->revenue === 500000);
    }

    public function test_yearly_report_aggregates_actual_paid_transactions(): void
    {
        $admin = $this->createAdmin();
        $this->createPayment($admin, '2025-12-28 09:00:00', 800000, 'da_thanh_toan');
        $this->createPayment($admin, '2026-01-08 09:00:00', 350000, 'da_thanh_toan');
        $this->createPayment($admin, '2026-02-02 09:00:00', 500000, 'da_thanh_toan');
        $this->createPayment($admin, '2026-03-02 09:00:00', 400000, 'cho_thanh_toan');

        $this->actingAsAdminWithTwoFactor($admin)
            ->get(route('admin.reports.revenue', ['period' => 'year']))
            ->assertOk()
            ->assertViewHas('totalRevenue', 1650000)
            ->assertViewHas('totalPayments', 3)
            ->assertViewHas('revenueByPeriod', fn ($rows): bool => (int) $rows->firstWhere('period_key', '2025')?->revenue === 800000
                && (int) $rows->firstWhere('period_key', '2026')?->revenue === 850000);
    }

    public function test_customer_cannot_access_revenue_report(): void
    {
        $customer = NguoiDung::factory()->createOne();

        $this->actingAs($customer)
            ->get(route('admin.reports.revenue'))
            ->assertForbidden();
    }

    public function test_dashboard_revenue_uses_successful_payments_instead_of_order_status(): void
    {
        $admin = $this->createAdmin();
        $this->createPayment($admin, now()->toDateTimeString(), 425000, 'da_thanh_toan');
        $this->createPayment($admin, now()->toDateTimeString(), 900000, 'cho_thanh_toan');
        $this->createPayment($admin, now()->toDateTimeString(), 300000, 'hoan_tien');

        $this->actingAsAdminWithTwoFactor($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('tongDoanhThu', 425000)
            ->assertViewHas('revenueChartValues', function (array $values): bool {
                return $values[(int) now()->format('n') - 1] === 425000;
            });
    }

    private function createPayment(NguoiDung $customer, string $paidAt, int $amount, string $status): void
    {
        $order = DonHang::create([
            'ma_don_hang' => 'RPT-'.Str::uuid(),
            'id_nguoi_dung' => $customer->id,
            'tong_tien' => $amount,
            'thanh_tien' => $amount,
        ]);

        ThanhToan::create([
            'id_don_hang' => $order->id,
            'phuong_thuc_thanh_toan' => 'COD',
            'so_tien' => $amount,
            'trang_thai' => $status,
            'ngay_thanh_toan' => $paidAt,
        ]);
    }

    private function createAdmin(): NguoiDung
    {
        $admin = NguoiDung::factory()->createOne();
        $adminRole = VaiTro::firstOrCreate(['ten_vai_tro' => 'admin']);
        $admin->vaiTro()->attach($adminRole);
        $admin->forceFill([
            'admin_totp_secret' => 'JBSWY3DPEHPK3PXP',
            'admin_totp_confirmed_at' => now(),
        ])->save();

        return $admin;
    }

    private function actingAsAdminWithTwoFactor(NguoiDung $admin): self
    {
        return $this->actingAs($admin)->withSession(['admin_2fa_verified_user' => $admin->id]);
    }
}
