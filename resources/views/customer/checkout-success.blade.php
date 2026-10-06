@extends('layouts.app')

@section('title', 'Đặt hàng thành công - Hóa đơn #' . $order->ma_don_hang . ' - BOOK & BOX')

@section('content')
    <div class="container py-5">
        {{-- Thanh nút hành động --}}
        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Tiếp tục mua sắm
            </a>
            <button onclick="window.print()" class="btn btn-dark rounded-pill px-4">
                <i class="bi bi-printer-fill me-2"></i> In Hóa Đơn (Bill)
            </button>
        </div>

        {{-- Khung Hóa Đơn Điện Tử --}}
        <div class="card border-0 shadow rounded-4 p-4 p-md-5 bg-white mx-auto" id="printableInvoice"
            style="max-width: 800px;">
            <!-- Header Bill -->
            <div class="row border-bottom pb-4 mb-4 align-items-center">
                <div class="col-sm-6 mb-3 mb-sm-0">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="fw-bold fs-3 text-dark">BOOK</span>
                        <span class="fw-bold fs-3 text-danger">BOX</span>
                    </div>
                    <small class="text-muted d-block">Hệ thống nhà sách trực tuyến BOOK & BOX</small>
                    <small class="text-muted d-block">Hotline: +84 767417206 | Website: bookbox.com</small>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <h4 class="fw-bold text-uppercase text-danger mb-1">Hóa Đơn Bán Hàng</h4>
                    <div class="fw-bold">Mã đơn: #{{ $order->ma_don_hang }}</div>
                    <small class="text-muted">Ngày đặt: {{ $order->ngay_tao }}</small>
                </div>
            </div>

            <!-- Thông tin giao hàng & Thanh toán -->
            @php
                $payment = $order->thanhToan;

                $paymentMethodLabels = [
                    'COD' => 'Thanh toán khi nhận hàng',
                    'MoMo' => 'MoMo',
                    'VNPay' => 'VNPay',
                    'BankTransfer' => 'Chuyển khoản VietQR',
                ];

                $paymentStatusLabels = [
                    'cho_thanh_toan' => 'Chờ thanh toán',
                    'da_thanh_toan' => 'Đã thanh toán',
                    'that_bai' => 'Thanh toán thất bại',
                    'hoan_tien' => 'Đã hoàn tiền',
                    'da_huy' => 'Đã hủy thanh toán',
                ];

                $orderStatusLabels = [
                    'cho_xu_ly' => 'Chờ xử lý',
                    'dang_xu_ly' => 'Đang xử lý',
                    'dang_giao' => 'Đang giao',
                    'hoan_thanh' => 'Hoàn thành',
                    'da_huy' => 'Đã hủy',
                ];

                $paymentMethod = $payment?->phuong_thuc_thanh_toan ?? 'COD';
                $paymentStatus = $payment?->trang_thai;
                $orderStatus = $order->trang_thai;

                $paymentStatusClass = match ($paymentStatus) {
                    'da_thanh_toan' => 'bg-success',
                    'that_bai', 'da_huy' => 'bg-danger',
                    'hoan_tien' => 'bg-info text-dark',
                    default => 'bg-warning-subtle text-warning-emphasis',
                };

                $orderStatusClass = match ($orderStatus) {
                    'hoan_thanh' => 'bg-success',
                    'da_huy' => 'bg-danger',
                    'dang_giao' => 'bg-primary',
                    'dang_xu_ly' => 'bg-info text-dark',
                    default => 'bg-warning text-dark',
                };
            @endphp

            <div class="row mb-4">
                <div class="col-sm-6 mb-3 mb-sm-0">
                    <h6 class="fw-bold text-dark border-bottom pb-1">Thông Tin Khách Hàng:</h6>
                    <div class="fw-bold">
                        {{ $order->ten_nguoi_nhan ?? ($order->nguoiDung?->ho_ten ?? 'N/A') }}
                    </div>
                    <div class="small text-muted mb-1">
                        SĐT: {{ $order->sdt_nguoi_nhan ?? 'N/A' }}
                    </div>
                    <div class="small text-muted">
                        Địa chỉ: {{ $order->dia_chi_nhan ?? ($order->dia_chi_giao_hang ?? 'N/A') }}
                    </div>
                </div>

                <div class="col-sm-6 text-sm-end">
                    <h6 class="fw-bold text-dark border-bottom pb-1">Thanh Toán & Trạng Thái:</h6>

                    <div class="small mb-1">
                        <strong>Phương thức:</strong>
                        <span class="badge bg-secondary">
                            {{ $paymentMethodLabels[$paymentMethod] ?? $paymentMethod }}
                        </span>
                    </div>

                    @if ($paymentStatus)
                        <div class="small mb-1">
                            <strong>Thanh toán:</strong>
                            <span class="badge {{ $paymentStatusClass }}">
                                {{ $paymentStatusLabels[$paymentStatus] ?? $paymentStatus }}
                            </span>
                        </div>
                    @endif

                    <div class="small">
                        <strong>Trạng thái đơn:</strong>
                        <span class="badge {{ $orderStatusClass }}">
                            {{ $orderStatusLabels[$orderStatus] ?? $orderStatus }}
                        </span>
                    </div>
                </div>
            </div>

            @if ($orderStatus === 'da_huy')
                <div class="alert alert-danger d-print-none">
                    Đơn hàng này đã bị hủy.
                </div>
            @elseif ($paymentStatus === 'da_thanh_toan')
                <div class="alert alert-success d-print-none">
                    Thanh toán đã được xác nhận cho đơn hàng này.
                </div>
            @elseif ($paymentStatus === 'hoan_tien')
                <div class="alert alert-info d-print-none">
                    Khoản thanh toán của đơn hàng này đã được hoàn tiền.
                </div>
            @elseif ($paymentStatus === 'that_bai')
                <div class="alert alert-danger d-print-none">
                    Thanh toán thất bại. Vui lòng xem chi tiết đơn hàng để biết cách thanh toán lại.
                </div>
            @elseif ($paymentMethod === 'COD')
                <div class="alert alert-info d-print-none">
                    Bạn sẽ thanh toán khi nhận hàng.
                </div>
            @else
                <div class="alert alert-warning d-print-none">
                    Đơn hàng đang chờ xác nhận thanh toán. Bạn có thể xem chi tiết đơn hàng để tiếp tục thanh toán.
                </div>
            @endif

            <!-- Bảng Sản phẩm -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Tên cuốn sách</th>
                            <th class="text-center">Đơn giá</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->chiTietDonHang as $index => $item)
                            <tr>
                                <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->sach->tieu_de }}</div>
                                    <small class="text-muted">ISBN: {{ $item->sach->ma_isbn ?? 'N/A' }}</small>
                                </td>
                                <td class="text-center">{{ number_format($item->don_gia, 0, ',', '.') }} đ</td>
                                <td class="text-center fw-bold">{{ $item->so_luong }}</td>
                                <td class="text-end fw-bold">{{ number_format($item->thanh_tien, 0, ',', '.') }} đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Giảm giá:</td>
                            <td class="text-end text-muted">{{ number_format($order->so_tien_giam_gia ?? 0, 0, ',', '.') }}
                                đ</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end fw-bold fs-5">Tổng tiền thanh toán:</td>
                            <td class="text-end fw-bold text-danger fs-5">
                                {{ number_format($order->thanh_tien, 0, ',', '.') }} đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Lời cảm ơn -->
            <div class="text-center border-top pt-4">
                <p class="fw-bold mb-1 text-dark">Cảm ơn bạn đã tin tưởng và mua sách tại BOOK & BOX!</p>
                <small class="text-muted">Mọi thắc mắc về hóa đơn xin vui lòng liên hệ bộ phận CSKH để được hỗ trợ.</small>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            #printableInvoice,
            #printableInvoice * {
                visibility: visible;
            }

            #printableInvoice {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none !important;
                shadow: none !important;
            }

            .d-print-none {
                display: none !important;
            }
        }
    </style>
@endsection
