@extends('admin.layouts.master')

@section('title', 'Quản lý Giao dịch Thanh toán - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Quản lý Giao dịch Thanh toán</h3>
                <p class="text-muted mb-0">Lịch sử thanh toán qua MoMo, VNPay và COD</p>
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
                        <label class="form-label fw-bold small">Cổng thanh toán</label>
                        <select name="method" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="cod" {{ request('method') == 'cod' ? 'selected' : '' }}>COD (Tiền mặt)
                            </option>
                            <option value="momo" {{ request('method') == 'momo' ? 'selected' : '' }}>MoMo</option>
                            <option value="vnpay" {{ request('method') == 'vnpay' ? 'selected' : '' }}>VNPay</option>
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
                            <option value="that_bai" {{ request('status') == 'that_bai' ? 'selected' : '' }}>Thất bại / Hủy
                            </option>
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
                                <th>Số tiền</th>
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
                                            #{{ $item->donHang->ma_don_hang ?? $item->id_don_hang }}
                                        </a>
                                    </td>
                                    <td><code class="text-dark">{{ $item->ma_giao_dich ?? 'N/A' }}</code></td>
                                    <td class="fw-bold text-danger">
                                        {{ number_format($item->so_tien ?? ($item->donHang->tong_tien ?? 0), 0, ',', '.') }}
                                        đ
                                    </td>
                                    <td>
                                        @php $method = strtolower($item->phuong_thuc); @endphp
                                        @if ($method === 'momo')
                                            <span class="badge bg-danger">MoMo</span>
                                        @elseif($method === 'vnpay')
                                            <span class="badge bg-primary">VNPay</span>
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

                                            @default
                                                <span class="badge bg-secondary">{{ $item->trang_thai }}</span>
                                        @endswitch
                                    </td>
                                    <td><small class="text-muted">{{ $item->ngay_tao }}</small></td>
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
