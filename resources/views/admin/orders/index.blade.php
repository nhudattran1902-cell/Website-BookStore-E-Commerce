@extends('admin.layouts.master')

@section('title', 'Quản lý Đơn hàng - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Quản lý Đơn hàng</h3>
                <p class="text-muted mb-0">Theo dõi khách hàng, trạng thái xử lý và giao nhận</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.orders.index') }}" method="GET" class="card border mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-sm-6 col-xl-4">
                        <label for="search" class="form-label small fw-semibold">Tìm đơn hàng</label>
                        <input id="search" type="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Mã đơn, người nhận, SĐT hoặc email">
                    </div>
                    <div class="col-sm-6 col-xl-2">
                        <label for="trang_thai" class="form-label small fw-semibold">Trạng thái đơn</label>
                        <select id="trang_thai" name="trang_thai" class="form-select form-select-sm">
                            <option value="">Tất cả trạng thái</option>
                            <option value="cho_xu_ly" @selected(request('trang_thai') === 'cho_xu_ly')>Chờ xử lý</option>
                            <option value="dang_xu_ly" @selected(request('trang_thai') === 'dang_xu_ly')>Đã xác nhận</option>
                            <option value="dang_giao" @selected(request('trang_thai') === 'dang_giao')>Đang giao</option>
                            <option value="hoan_thanh" @selected(request('trang_thai') === 'hoan_thanh')>Đã giao</option>
                            <option value="da_huy" @selected(request('trang_thai') === 'da_huy')>Đã hủy</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-xl-2">
                        <label for="don_vi_van_chuyen" class="form-label small fw-semibold">Đơn vị vận chuyển</label>
                        <select id="don_vi_van_chuyen" name="don_vi_van_chuyen" class="form-select form-select-sm">
                            <option value="">Tất cả đơn vị</option>
                            <option value="GHN" @selected(request('don_vi_van_chuyen') === 'GHN')>GHN</option>
                            <option value="GHTK" @selected(request('don_vi_van_chuyen') === 'GHTK')>GHTK</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-xl-2">
                        <label for="ngoai_le" class="form-label small fw-semibold">Tình trạng giao</label>
                        <select id="ngoai_le" name="ngoai_le" class="form-select form-select-sm">
                            <option value="">Tất cả tình trạng</option>
                            <option value="giao_that_bai" @selected(request('ngoai_le') === 'giao_that_bai')>Giao thất bại</option>
                            <option value="rto_chuyen_hoan" @selected(request('ngoai_le') === 'rto_chuyen_hoan')>Chuyển hoàn</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-xl-2">
                        <label for="sort_total" class="form-label small fw-semibold">Sắp xếp tổng đơn</label>
                        <select id="sort_total" name="sort_total" class="form-select form-select-sm">
                            <option value="">Ngày đặt mới nhất</option>
                            <option value="desc" @selected(request('sort_total') === 'desc')>Giá từ cao đến thấp</option>
                            <option value="asc" @selected(request('sort_total') === 'asc')>Giá từ thấp đến cao</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-xl-2">
                        <label for="date_from" class="form-label small fw-semibold">Đặt từ ngày</label>
                        <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-sm-6 col-xl-2">
                        <label for="date_to" class="form-label small fw-semibold">Đến ngày</label>
                        <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-sm-6 col-xl-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Lọc đơn hàng</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">Xóa lọc</a>
                    </div>
                </div>
            </div>
        </form>

        {{-- Bảng danh sách đơn hàng --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3" style="width: 110px;">Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Tổng đơn</th>
                                <th>Vận chuyển</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                                <th class="text-end pe-3">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">#{{ $order->ma_don_hang ?? $order->id }}</td>
                                    <td>
                                        <div class="fw-bold">
                                            {{ $order->nguoiDung->ho_ten ?? ($order->ten_nguoi_nhan ?? 'Khách lẻ') }}</div>
                                        <small class="text-muted">{{ $order->sdt_nguoi_nhan ?? '' }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-danger">
                                            {{ number_format($order->thanh_tien ?? $order->thanhToan?->so_tien ?? $order->tong_tien ?? 0, 0, ',', '.') }} đ
                                        </div>
                                        <small class="text-muted d-block">
                                            Tạm tính: {{ number_format($order->tong_tien ?? 0, 0, ',', '.') }} đ
                                        </small>
                                        @if (($order->so_tien_giam_gia ?? 0) > 0)
                                            <small class="text-success d-block">
                                                Giảm: -{{ number_format($order->so_tien_giam_gia, 0, ',', '.') }} đ
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($order->don_vi_van_chuyen || $order->ma_van_don)
                                            <div class="fw-semibold">{{ $order->don_vi_van_chuyen ?: 'Chưa chọn hãng' }}</div>
                                            <small class="text-muted">{{ $order->ma_van_don ?: 'Chưa có mã vận đơn' }}</small>
                                        @else
                                            <span class="text-muted">Chưa tạo vận đơn</span>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($order->trang_thai)
                                            @case('cho_xu_ly')
                                                <span class="badge bg-warning-subtle text-warning-emphasis"><i class="bi bi-clock me-1"></i>Chờ xử lý</span>
                                            @break

                                            @case('dang_xu_ly')
                                                <span class="badge bg-info-subtle text-info-emphasis"><i class="bi bi-check2-circle me-1"></i>Đã xác nhận</span>
                                            @break

                                            @case('dang_giao')
                                                <span class="badge bg-primary-subtle text-primary-emphasis"><i class="bi bi-truck me-1"></i>Đang giao</span>
                                            @break

                                            @case('hoan_thanh')
                                                <span class="badge bg-success-subtle text-success-emphasis"><i class="bi bi-check-circle me-1"></i>Đã giao</span>
                                            @break

                                            @case('da_huy')
                                                <span class="badge bg-danger-subtle text-danger-emphasis"><i class="bi bi-x-circle me-1"></i>Đã hủy</span>
                                            @break

                                            @default
                                                <span class="badge bg-secondary">{{ $order->trang_thai }}</span>
                                        @endswitch
                                    </td>
                                    <td><small class="text-muted">{{ $order->ngay_tao?->format('d/m/Y H:i') ?? $order->ngay_tao }}</small></td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark">
                                                <i class="bi bi-eye"></i> Chi tiết
                                            </a>
                                            @if (auth()->user()->hasPermission('orders.status.update'))
                                            <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="d-flex gap-1">
                                                @csrf
                                                @method('PUT')
                                                <select name="trang_thai" class="form-select form-select-sm" aria-label="Cập nhật trạng thái đơn #{{ $order->id }}">
                                                    <option value="cho_xu_ly" @selected($order->trang_thai === 'cho_xu_ly')>Chờ xử lý</option>
                                                    <option value="dang_xu_ly" @selected($order->trang_thai === 'dang_xu_ly')>Đã xác nhận</option>
                                                    <option value="dang_giao" @selected($order->trang_thai === 'dang_giao')>Đang giao</option>
                                                    <option value="hoan_thanh" @selected($order->trang_thai === 'hoan_thanh')>Đã giao</option>
                                                    <option value="da_huy" @selected($order->trang_thai === 'da_huy')>Đã hủy</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Cập nhật trạng thái"><i class="bi bi-check2"></i></button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Chưa có đơn hàng nào phù hợp bộ lọc.
                                            thống.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if (method_exists($orders, 'links'))
                    <div class="card-footer bg-transparent border-0 d-flex justify-content-end pt-3">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endsection
