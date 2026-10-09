@extends('admin.layouts.master')

@section('title', 'Báo cáo doanh thu - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Báo cáo doanh thu</h1>
                <p class="text-muted mb-0">Doanh thu được tính theo giao dịch đã thanh toán thành công.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('admin.reports.revenue') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="period" class="form-label fw-semibold">Thống kê theo</label>
                        <select id="period" name="period" class="form-select" onchange="this.form.submit()">
                            <option value="day" @selected($period === 'day')>Ngày trong tháng</option>
                            <option value="month" @selected($period === 'month')>Tháng trong năm</option>
                            <option value="year" @selected($period === 'year')>Năm</option>
                        </select>
                    </div>
                    @if ($period !== 'year')
                        <div class="col-md-3">
                            <label for="year" class="form-label fw-semibold">Năm</label>
                            <select id="year" name="year" class="form-select">
                                @for ($optionYear = $currentYear; $optionYear >= 2000; $optionYear--)
                                    <option value="{{ $optionYear }}" @selected($year === $optionYear)>{{ $optionYear }}</option>
                                @endfor
                            </select>
                        </div>
                    @endif
                    @if ($period === 'day')
                        <div class="col-md-3">
                            <label for="month" class="form-label fw-semibold">Tháng</label>
                            <select id="month" name="month" class="form-select">
                                @for ($optionMonth = 1; $optionMonth <= 12; $optionMonth++)
                                    <option value="{{ $optionMonth }}" @selected($month === $optionMonth)>Tháng {{ $optionMonth }}</option>
                                @endfor
                            </select>
                        </div>
                    @endif
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Xem báo cáo</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Tổng tiền đã thu</div>
                        <div class="h3 fw-bold text-success mb-0">{{ number_format($totalRevenue, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Số giao dịch đã thanh toán</div>
                        <div class="h3 fw-bold mb-0">{{ number_format($totalPayments) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h2 class="h5 fw-bold mb-0">
                    @if ($period === 'day')
                        Doanh thu từng ngày trong tháng {{ $month }}/{{ $year }}
                    @elseif ($period === 'month')
                        Doanh thu từng tháng năm {{ $year }}
                    @else
                        Doanh thu từng năm
                    @endif
                </h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">{{ $period === 'day' ? 'Ngày' : ($period === 'month' ? 'Tháng' : 'Năm') }}</th>
                            <th class="text-end">Số giao dịch</th>
                            <th class="text-end pe-3">Doanh thu đã thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($revenueByPeriod as $row)
                            <tr>
                                <td class="ps-3">{{ $row->period_key }}</td>
                                <td class="text-end">{{ number_format($row->payment_count) }}</td>
                                <td class="text-end pe-3 fw-semibold">{{ number_format($row->revenue, 0, ',', '.') }} đ</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Không có giao dịch đã thanh toán trong kỳ này.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white text-muted small">
                Chỉ tính trạng thái “Đã thanh toán” có thời điểm thanh toán. Giao dịch chờ, thất bại và hoàn tiền không được cộng vào doanh thu.
            </div>
        </div>
    </div>
@endsection
