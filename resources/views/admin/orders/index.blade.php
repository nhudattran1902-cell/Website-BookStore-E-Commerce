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
                                <th class="text-end pe-3">Hành động</th>
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
                                            $pt = strtolower(
                                                $order->thanhToan->phuong_thuc ??
                                                    ($order->phuong_thuc_thanh_toan ?? 'cod'),
                                            );
                                        @endphp
                                        @if ($pt === 'momo')
                                            <span class="badge bg-danger">MoMo</span>
                                        @elseif($pt === 'vnpay')
                                            <span class="badge bg-primary">VNPay</span>
                                        @else
                                            <span class="badge bg-secondary">COD (Tiền mặt)</span>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($order->trang_thai)
                                            @case('cho_xu_ly')
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Chờ xử
                                                    lý</span>
                                            @break

                                            @case('dang_xu_ly')
                                                <span class="badge bg-info text-dark"><i
                                                        class="bi bi-gear-wide-connected me-1"></i>Đang xử lý</span>
                                            @break

                                            @case('dang_giao')
                                                <span class="badge bg-primary"><i class="bi bi-truck me-1"></i>Đang giao</span>
                                            @break

                                            @case('hoan_thanh')
                                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Hoàn
                                                    thành</span>
                                            @break

                                            @case('da_huy')
                                                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Đã hủy</span>
                                            @break

                                            @default
                                                <span class="badge bg-secondary">{{ $order->trang_thai }}</span>
                                        @endswitch
                                    </td>
                                    <td><small class="text-muted">{{ $order->ngay_tao }}</small></td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('admin.orders.show', $order->id) }}"
                                            class="btn btn-sm btn-outline-dark">
                                            <i class="bi bi-eye"></i> Chi tiết
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Chưa có đơn hàng nào trong hệ
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
