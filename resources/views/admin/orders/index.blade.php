@extends('admin.layouts.master')

@section('title', 'Quản lý Đơn hàng - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Quản lý Đơn hàng</h3>
                <p class="text-muted mb-0">Danh sách và trạng thái xử lý đơn hàng</p>
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
                    <div class="col-sm-6 col-lg-2">
                        <label for="sort_total" class="form-label small fw-semibold">Sắp xếp tổng tiền</label>
                        <select id="sort_total" name="sort_total" class="form-select form-select-sm">
                            <option value="">Ngày đặt mới nhất</option>
                            <option value="desc" @selected(request('sort_total') === 'desc')>Giá từ cao đến thấp</option>
                            <option value="asc" @selected(request('sort_total') === 'asc')>Giá từ thấp đến cao</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label for="phuong_thuc_thanh_toan" class="form-label small fw-semibold">Thanh toán</label>
                        <select id="phuong_thuc_thanh_toan" name="phuong_thuc_thanh_toan" class="form-select form-select-sm">
                            <option value="">Tất cả phương thức</option>
                            <option value="COD" @selected(request('phuong_thuc_thanh_toan') === 'COD')>COD</option>
                            <option value="VNPay" @selected(request('phuong_thuc_thanh_toan') === 'VNPay')>Chuyển khoản / thẻ (VNPay)</option>
                            <option value="MoMo" @selected(request('phuong_thuc_thanh_toan') === 'MoMo')>Ví điện tử MoMo</option>
                            <option value="BankTransfer" @selected(request('phuong_thuc_thanh_toan') === 'BankTransfer')>Chuyển khoản VietQR</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-2">
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
                    <div class="col-sm-6 col-lg-2">
                        <label for="date_from" class="form-label small fw-semibold">Từ ngày</label>
                        <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label for="date_to" class="form-label small fw-semibold">Đến ngày</label>
                        <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-sm-6 col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Lọc dữ liệu</button>
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
                                <th>Tổng tiền</th>
                                <th>Phương thức</th>
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
                                    <td class="fw-bold text-danger">
                                        {{ number_format($order->tong_tien ?? 0, 0, ',', '.') }} đ
                                    </td>
                                    <td>
                                        @php
                                            $paymentMethod = $order->thanhToan->phuong_thuc_thanh_toan ?? 'COD';
                                        @endphp
                                        <span class="badge {{ $paymentMethod === 'MoMo' ? 'bg-warning text-dark' : ($paymentMethod === 'VNPay' ? 'bg-info text-dark' : 'bg-secondary-subtle text-secondary') }}">
                                            {{ $paymentMethod === 'MoMo' ? 'Ví MoMo' : ($paymentMethod === 'VNPay' ? 'VNPay' : 'COD') }}
                                        </span>
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
