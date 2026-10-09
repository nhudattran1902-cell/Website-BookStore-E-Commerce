<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThanhToan;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RevenueReportController extends Controller
{
    public function index(Request $request): View
    {
        $currentYear = (int) now()->format('Y');
        $filters = $request->validate([
            'period' => ['sometimes', Rule::in(['day', 'month', 'year'])],
            'year' => ['sometimes', 'integer', 'between:2000,'.$currentYear],
            'month' => ['sometimes', 'integer', 'between:1,12'],
        ]);

        $period = $filters['period'] ?? 'month';
        $year = (int) ($filters['year'] ?? $currentYear);
        $month = (int) ($filters['month'] ?? now()->format('n'));
        $query = ThanhToan::query()
            ->where('trang_thai', 'da_thanh_toan')
            ->whereNotNull('ngay_thanh_toan');

        if ($period === 'day') {
            $start = CarbonImmutable::create($year, $month, 1)->startOfMonth();
            $end = $start->endOfMonth();
            $query->whereBetween('ngay_thanh_toan', [$start, $end]);
        } elseif ($period === 'month') {
            $start = CarbonImmutable::create($year, 1, 1)->startOfYear();
            $end = $start->endOfYear();
            $query->whereBetween('ngay_thanh_toan', [$start, $end]);
        } else {
            $query->where('ngay_thanh_toan', '<=', now());
        }

        $groupExpression = $this->groupExpression($period);
        $revenueByPeriod = $query
            ->selectRaw("{$groupExpression} as period_key, COUNT(*) as payment_count, SUM(so_tien) as revenue")
            ->groupByRaw($groupExpression)
            ->orderByRaw($groupExpression)
            ->get();

        return view('admin.reports.revenue', [
            'revenueByPeriod' => $revenueByPeriod,
            'totalRevenue' => (int) $revenueByPeriod->sum('revenue'),
            'totalPayments' => (int) $revenueByPeriod->sum('payment_count'),
            'period' => $period,
            'year' => $year,
            'month' => $month,
            'currentYear' => $currentYear,
        ]);
    }

    private function groupExpression(string $period): string
    {
        $driver = DB::connection()->getDriverName();

        $expressions = match ($driver) {
            'sqlite' => [
                'day' => 'date(ngay_thanh_toan)',
                'month' => "strftime('%Y-%m', ngay_thanh_toan)",
                'year' => "strftime('%Y', ngay_thanh_toan)",
            ],
            'pgsql' => [
                'day' => "to_char(ngay_thanh_toan, 'YYYY-MM-DD')",
                'month' => "to_char(ngay_thanh_toan, 'YYYY-MM')",
                'year' => "to_char(ngay_thanh_toan, 'YYYY')",
            ],
            default => [
                'day' => 'DATE(ngay_thanh_toan)',
                'month' => "DATE_FORMAT(ngay_thanh_toan, '%Y-%m')",
                'year' => "DATE_FORMAT(ngay_thanh_toan, '%Y')",
            ],
        };

        return $expressions[$period];
    }
}
