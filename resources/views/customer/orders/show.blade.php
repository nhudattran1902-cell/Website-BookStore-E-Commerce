@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->ma_don_hang)

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Chi tiết đơn hàng #{{ $order->ma_don_hang }}</h3>
            <p class="text-muted mb-0">Ngày đặt: {{ date('d/m/Y H:i', strtotime($order->ngay_tao)) }}</p>
        </div>
        <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <div class="row">
        <!-- Danh sách sản phẩm trong đơn hàng -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Sản phẩm đã mua</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Sách</th>
                                    <th class="text-center">Đơn giá</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end pe-3">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->chiTietDonHang as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ isset($item->sach->anh_bia) && $item->sach->anh_bia ? asset('storage/' . $item->sach->anh_bia) : asset('images/no-cover.jpg') }}" 
                                                     alt="{{ $item->sach->tieu_de ?? 'Sách' }}" 
                                                     class="rounded me-3 shadow-sm" style="width: 50px; height: 70px; object-fit: cover;">
                                                <div>
                                                    <h6 class="fw-bold mb-1">{{ $item->sach->tieu_de ?? 'Sách đã bị xóa khỏi hệ thống' }}</h6>
                                                    <small class="text-muted">Mã sách: #{{ $item->id_sach }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">{{ number_format($item->don_gia, 0, ',', '.') }} đ</td>
                                        <td class="text-center fw-bold">x{{ $item->so_luong }}</td>
                                        <td class="text-end pe-3 fw-bold text-success">
                                            {{ number_format($item->thanh_tien, 0, ',', '.') }} đ
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Không tìm thấy chi tiết sản phẩm nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin vận chuyển & Tổng tiền -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Thông tin giao hàng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Trạng thái đơn hàng</label>
                        <div>
                            @switch($order->trang_thai)
                                @case('cho_xu_ly') <span class="badge bg-warning text-dark">Chờ xử lý</span> @break
                                @case('dang_xu_ly') <span class="badge bg-info text-dark">Đang xử lý</span> @break
                                @case('dang_giao') <span class="badge bg-primary">Đang giao hàng</span> @break
                                @case('hoan_thanh') <span class="badge bg-success">Hoàn thành</span> @break
                                @case('da_huy') <span class="badge bg-danger">Đã hủy</span> @break
                                @default <span class="badge bg-secondary">{{ $order->trang_thai }}</span>
                            @endswitch
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Địa chỉ nhận hàng</label>
                        <p class="fw-semibold mb-0">{{ $order->dia_chi_giao_hang }}</p>
                    </div>
                    @if($order->ghi_chu)
                        <div class="mb-3">
                            <label class="text-muted small">Ghi chú từ khách hàng</label>
                            <p class="text-muted mb-0">{{ $order->ghi_chu }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Thanh toán</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tổng tiền hàng:</span>
                        <span>{{ number_format($order->tong_tien, 0, ',', '.') }} đ</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Giảm giá:</span>
                        <span class="text-danger">-{{ number_format($order->so_tien_giam_gia, 0, ',', '.') }} đ</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5 text-success">
                        <span>Tổng thanh toán:</span>
                        <span>{{ number_format($order->thanh_tien, 0, ',', '.') }} đ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection