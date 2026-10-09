@extends('admin.layouts.master')

@section('title', 'Quản lý Giao dịch Thanh toán - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Đối soát giao dịch thanh toán</h3>
                <p class="text-muted mb-0">Theo dõi mã giao dịch, trạng thái và chênh lệch số tiền. Thông tin sách, người nhận và vận chuyển nằm trong chi tiết đơn hàng.</p>
            </div>
        </div>

        {{-- Bộ lọc nâng cao --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('admin.transactions.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Từ khóa</label>
                        <input type="text" name="search" class="form-control" placeholder="Mã đơn / Mã giao dịch..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Phương thức / cổng</label>
                        <select name="method" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="COD" @selected(request('method') === 'COD')>COD (Thu hộ)
                            </option>
                            <option value="MoMo" @selected(request('method') === 'MoMo')>MoMo</option>
                            <option value="VNPay" @selected(request('method') === 'VNPay')>VNPay</option>
                            <option value="BankTransfer" @selected(request('method') === 'BankTransfer')>Chuyển khoản VietQR</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="da_thanh_toan" {{ request('status') == 'da_thanh_toan' ? 'selected' : '' }}>Thành
                                công</option>
                            <option value="cho_thanh_toan" {{ request('status') == 'cho_thanh_toan' ? 'selected' : '' }}>Chờ
                                thanh toán</option>
                            <option value="that_bai" {{ request('status') == 'that_bai' ? 'selected' : '' }}>Thất bại
                            </option>
                            <option value="da_huy" @selected(request('status') === 'da_huy')>Đã hủy</option>
                            <option value="hoan_tien" @selected(request('status') === 'hoan_tien')>Đã hoàn tiền</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Từ ngày</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Đến ngày</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end gap-1">
                        <button type="submit" class="btn btn-primary w-100" title="Tìm kiếm"><i
                                class="bi bi-search"></i></button>
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary"
                            title="Đặt lại"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bảng giao dịch --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3" style="width: 70px;">ID</th>
                                <th>Mã đơn hàng</th>
                                <th>Mã giao dịch</th>
                                <th>Đối chiếu số tiền</th>
                                <th>Cổng thanh toán</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th class="text-end pe-3">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $item)
                                <tr>
                                    <td class="ps-3 fw-bold">#{{ $item->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $item->id_don_hang) }}"
                                            class="fw-bold text-primary text-decoration-none">
                                            {{ $item->donHang->ma_don_hang ?? $item->id_don_hang }}
                                        </a>
                                    </td>
                                    <td><code class="text-dark">{{ $item->ma_giao_dich ?: 'Chưa có mã' }}</code></td>
                                    <td>
                                        @php
                                            $expectedAmount = $item->donHang?->thanh_tien;
                                            $recordedAmount = $item->so_tien;
                                            $amountDifference = $expectedAmount !== null && $recordedAmount !== null
                                                ? (int) $recordedAmount - (int) $expectedAmount
                                                : null;
                                        @endphp
                                        <div class="fw-bold text-dark">
                                            Bản ghi: {{ number_format($recordedAmount ?? 0, 0, ',', '.') }} đ
                                        </div>
                                        @if ($expectedAmount !== null)
                                            <small class="text-muted d-block">Theo đơn: {{ number_format($expectedAmount, 0, ',', '.') }} đ</small>
                                            @if ($amountDifference === 0)
                                                <span class="badge bg-success-subtle text-success-emphasis mt-1">Khớp số tiền</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger-emphasis mt-1">
                                                    Lệch {{ $amountDifference > 0 ? '+' : '−' }}{{ number_format(abs($amountDifference), 0, ',', '.') }} đ
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @php $method = $item->phuong_thuc_thanh_toan; @endphp
                                        @if ($method === 'MoMo')
                                            <span class="badge bg-danger">MoMo</span>
                                        @elseif($method === 'VNPay')
                                            <span class="badge bg-primary">VNPay</span>
                                        @elseif($method === 'BankTransfer')
                                            <span class="badge bg-success">VietQR</span>
                                        @else
                                            <span class="badge bg-secondary">COD</span>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($item->trang_thai)
                                            @case('da_thanh_toan')
                                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Thành
                                                    công</span>
                                            @break

                                            @case('cho_thanh_toan')
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Chờ xử
                                                    lý</span>
                                            @break

                                            @case('that_bai')
                                                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Thất bại</span>
                                            @break

                                            @case('da_huy')
                                                <span class="badge bg-secondary"><i class="bi bi-slash-circle me-1"></i>Đã hủy</span>
                                            @break

                                            @case('hoan_tien')
                                                <span class="badge bg-info text-dark"><i class="bi bi-arrow-counterclockwise me-1"></i>Đã hoàn tiền</span>
                                            @break

                                            @default
                                                <span class="badge bg-secondary">{{ $item->trang_thai }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <small class="text-muted d-block">Tạo: {{ $item->ngay_tao }}</small>
                                        @if ($item->ngay_thanh_toan)
                                            <small class="text-success d-block">Thanh toán: {{ $item->ngay_thanh_toan }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('admin.transactions.show', $item->id) }}"
                                            class="btn btn-sm btn-outline-dark">
                                            <i class="bi bi-eye me-1"></i> Chi tiết
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Không tìm thấy giao dịch thanh
                                            toán nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if (method_exists($transactions, 'links'))
                    <div class="card-footer bg-transparent border-0 d-flex justify-content-end pt-3">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endsection
