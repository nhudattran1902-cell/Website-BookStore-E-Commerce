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

    @if (session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif

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

    @if ($order->lichSuDonHang->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-4 mb-4 p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-danger"></i>Lịch sử cập nhật</h6>
            <ol class="list-unstyled mb-0">
                @foreach ($order->lichSuDonHang as $event)
                    <li class="border-start border-2 ps-3 pb-3 ms-1">
                        <div class="fw-semibold">{{ $event->ghi_chu ?: $event->trang_thai_moi }}</div>
                        <div class="small text-muted">
                            {{ $event->ngay_tao }}
                            @if ($event->vi_tri)
                                <span class="mx-1">·</span>{{ $event->vi_tri }}
                            @endif
                            @if ($event->nguoiThayDoi)
                                <span class="mx-1">·</span>{{ $event->nguoiThayDoi->ho_ten }}
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

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
                                                <img src="{{ $item->sach?->anh_bia_url ?: asset('images/no-cover.jpg') }}"
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
                <p class="mb-3"><strong>Địa chỉ:</strong> {{ $order->dia_chi_nhan ?: $order->dia_chi_giao_hang }}</p>

                @if ($order->ma_van_don)
                    <p class="mb-2"><strong>Đơn vị vận chuyển:</strong> {{ $order->don_vi_van_chuyen ?: 'Chưa cập nhật' }}</p>
                    <p class="mb-3"><strong>Mã vận đơn:</strong> {{ $order->ma_van_don }}</p>
                    @if ($order->tracking_url)
                        <a class="btn btn-outline-primary btn-sm mb-3" href="{{ $order->tracking_url }}" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Theo dõi vận đơn
                        </a>
                    @endif
                @endif

                @if (in_array($order->trang_thai, ['cho_xu_ly', 'dang_xu_ly'], true))
                    <details class="mb-4">
                        <summary class="fw-semibold text-primary">Chỉnh sửa thông tin nhận hàng</summary>
                        <form action="{{ route('customer.orders.updateAddress', $order->id) }}" method="POST" class="pt-3">
                            @csrf
                            @method('PATCH')
                            <div class="mb-2">
                                <label for="ten_nguoi_nhan" class="form-label small">Người nhận</label>
                                <input id="ten_nguoi_nhan" name="ten_nguoi_nhan" class="form-control" value="{{ $order->ten_nguoi_nhan }}" required maxlength="150">
                            </div>
                            <div class="mb-2">
                                <label for="sdt_nguoi_nhan" class="form-label small">Số điện thoại</label>
                                <input id="sdt_nguoi_nhan" name="sdt_nguoi_nhan" class="form-control" value="{{ $order->sdt_nguoi_nhan }}" required maxlength="20">
                            </div>
                            <div class="mb-3">
                                <label for="dia_chi_nhan" class="form-label small">Địa chỉ</label>
                                <textarea id="dia_chi_nhan" name="dia_chi_nhan" class="form-control" rows="3" required maxlength="500">{{ $order->dia_chi_nhan ?: $order->dia_chi_giao_hang }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Lưu thông tin</button>
                        </form>
                    </details>
                @endif

                <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">
                    <i class="bi bi-wallet2 me-2 text-danger"></i> Thanh Toán
                </h6>
                @php
                    $paymentMethod = $order->thanhToan->phuong_thuc_thanh_toan ?? 'COD';
                    $paymentMethodLabels = [
                        'BankTransfer' => 'Chuyển khoản VietQR',
                        'VNPay' => 'VNPay',
                        'MoMo' => 'MoMo',
                        'COD' => 'Thanh toán khi nhận hàng',
                    ];
                @endphp
                <p class="mb-2">
                    <strong>Phương thức:</strong> 
                    <span class="badge bg-secondary">{{ $paymentMethodLabels[$paymentMethod] ?? $paymentMethod }}</span>
                </p>
                <p class="mb-0">
                    <strong>Trạng thái:</strong> 
                    @php
                        $paymentStatus = $order->thanhToan->trang_thai ?? 'cho_thanh_toan';
                        $paymentLabels = [
                            'cho_thanh_toan' => 'Chờ thanh toán',
                            'da_thanh_toan' => 'Đã thanh toán',
                            'that_bai' => 'Thanh toán thất bại',
                            'hoan_tien' => 'Đã hoàn tiền',
                            'da_huy' => 'Đã hủy thanh toán',
                        ];
                    @endphp
                    <span class="badge {{ $paymentStatus === 'da_thanh_toan' ? 'bg-success' : (in_array($paymentStatus, ['that_bai', 'da_huy'], true) ? 'bg-danger' : 'bg-warning text-dark') }}">
                        {{ $paymentLabels[$paymentStatus] ?? $paymentStatus }}
                    </span>
                </p>
                @if ($order->trang_thai === 'cho_xu_ly'
                    && $order->thanhToan
                    && $order->thanhToan->phuong_thuc_thanh_toan !== 'COD'
                    && in_array($paymentStatus, ['cho_thanh_toan', 'that_bai'], true)
                    && (! $order->thanh_toan_het_han_at || $order->thanh_toan_het_han_at->isFuture()))
                    <a href="{{ route('checkout.payment.start', $order->ma_don_hang) }}" class="btn btn-danger btn-sm mt-3">
                        Tiếp tục thanh toán
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection