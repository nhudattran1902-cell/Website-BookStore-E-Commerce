@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->ma_don_hang . ' - BOOK & BOX')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Chi Tiết Đơn Hàng #{{ $order->ma_don_hang }}</h3>
            <p class="text-muted mb-0">Thời gian đặt: {{ $order->ngay_tao }}</p>
        </div>
        <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Quay lại lịch sử
        </a>
    </div>

    {{-- TIMELINE TIẾN TRÌNH GIAO HÀNG --}}
    @php
        $statuses = [
            'cho_xu_ly' => ['title' => 'Chờ xử lý', 'icon' => 'bi-clock'],
            'dang_xu_ly' => ['title' => 'Đang xử lý', 'icon' => 'bi-box-seam'],
            'dang_giao' => ['title' => 'Đang giao hàng', 'icon' => 'bi-truck'],
            'hoan_thanh' => ['title' => 'Hoàn thành', 'icon' => 'bi-check-circle-fill'],
        ];
        $isCanceled = $order->trang_thai === 'da_huy';
        $statusKeys = array_keys($statuses);
        $currentIndex = array_search($order->trang_thai, $statusKeys);
    @endphp

    <div class="card border-0 shadow-sm rounded-4 mb-4 p-4">
        <h6 class="fw-bold mb-4 text-dark"><i class="bi bi-signpost-split me-2 text-danger"></i>Tiến Trình Đơn Hàng</h6>
        
        @if($isCanceled)
            <div class="alert alert-danger text-center mb-0 rounded-3">
                <i class="bi bi-x-circle-fill me-2 fs-5"></i> <strong>ĐƠN HÀNG NÀY ĐÃ BỊ HỦY</strong>
            </div>
        @else
            <div class="d-flex justify-content-between position-relative px-2 px-md-5">
                @foreach($statuses as $key => $info)
                    @php
                        $stepIndex = array_search($key, $statusKeys);
                        $isDone = $currentIndex !== false && $stepIndex <= $currentIndex;
                    @endphp
                    <div class="text-center position-relative" style="z-index: 2;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 {{ $isDone ? 'bg-success text-white' : 'bg-light text-muted border' }}" 
                             style="width: 50px; height: 50px; font-size: 1.25rem;">
                            <i class="bi {{ $info['icon'] }}"></i>
                        </div>
                        <small class="fw-bold d-block {{ $isDone ? 'text-success' : 'text-muted' }}">{{ $info['title'] }}</small>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="row g-4">
        <!-- Danh sách sách đã đặt -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white fw-bold py-3">
                    <i class="bi bi-journal-bookmark me-2"></i> Danh sách sách đã đặt
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
                                @foreach($order->chiTietDonHang as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $item->sach->anh_bia ? asset('storage/' . $item->sach->anh_bia) : asset('images/no-cover.jpg') }}" 
                                                     class="rounded me-3" style="width: 45px; height: 60px; object-fit: cover;">
                                                <span class="fw-bold text-dark">{{ $item->sach->tieu_de }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">{{ number_format($item->don_gia, 0, ',', '.') }} đ</td>
                                        <td class="text-center fw-bold">{{ $item->so_luong }}</td>
                                        <td class="text-end pe-3 fw-bold text-danger">
                                            {{ number_format($item->thanh_tien, 0, ',', '.') }} đ
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Tổng thanh toán:</td>
                                    <td class="text-end pe-3 fw-bold text-danger fs-5">
                                        {{ number_format($order->thanh_tien, 0, ',', '.') }} đ
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin giao nhận -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                    <i class="bi bi-person-lines-fill me-2 text-danger"></i> Thông Tin Giao Nhận
                </h6>
                <p class="mb-2"><strong>Người nhận:</strong> {{ $order->ten_nguoi_nhan ?? $order->nguoiDung->ho_ten }}</p>
                <p class="mb-2"><strong>Số điện thoại:</strong> {{ $order->sdt_nguoi_nhan ?? 'N/A' }}</p>
                <p class="mb-3"><strong>Địa chỉ:</strong> {{ $order->dia_chi_giao_hang }}</p>

                <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                    <i class="bi bi-wallet2 me-2 text-danger"></i> Thanh Toán
                </h6>
                <p class="mb-2">
                    <strong>Phương thức:</strong> 
                    <span class="badge bg-secondary">{{ $order->thanhToan->phuong_thuc_thanh_toan ?? 'COD' }}</span>
                </p>
                <p class="mb-0">
                    <strong>Trạng thái:</strong> 
                    <span class="badge bg-success">Đã xác nhận</span>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection