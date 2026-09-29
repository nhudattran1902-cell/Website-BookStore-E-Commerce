@extends('admin.layouts.master')

@section('title', 'Chi tiết Giao dịch #' . $transaction->id . ' - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Chi tiết Giao dịch #{{ $transaction->id }}</h3>
                <p class="text-muted mb-0">Thời gian tạo: {{ $transaction->ngay_tao }}</p>
            </div>
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <div class="row">
            <!-- Thông tin giao dịch -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-dark text-white fw-bold">
                        <i class="bi bi-credit-card me-2"></i> Thông tin thanh toán
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width: 180px;">Mã giao dịch:</td>
                                <td class="fw-bold"><code>{{ $transaction->ma_giao_dich ?? 'Không có (COD)' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Cổng thanh toán:</td>
                                <td>
                                    @php $method = strtolower($transaction->phuong_thuc); @endphp
                                    @if ($method === 'momo')
                                        <span class="badge bg-danger">MoMo</span>
                                    @elseif($method === 'vnpay')
                                        <span class="badge bg-primary">VNPay</span>
                                    @else
                                        <span class="badge bg-secondary">COD (Tiền mặt)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Số tiền thanh toán:</td>
                                <td class="fw-bold text-danger fs-5">
                                    {{ number_format($transaction->so_tien ?? ($transaction->donHang->tong_tien ?? 0), 0, ',', '.') }}
                                    đ
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Trạng thái:</td>
                                <td>
                                    @switch($transaction->trang_thai)
                                        @case('da_thanh_toan')
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Thành công</span>
                                        @break

                                        @case('cho_thanh_toan')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Chờ xử
                                                lý</span>
                                        @break

                                        @case('that_bai')
                                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Thất bại</span>
                                        @break

                                        @default
                                            <span class="badge bg-secondary">{{ $transaction->trang_thai }}</span>
                                    @endswitch
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Ghi chú / Phản hồi:</td>
                                <td>{{ $transaction->ghi_chu ?? 'Không có' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Thông tin Đơn hàng & Khách hàng -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-dark text-white fw-bold">
                        <i class="bi bi-receipt me-2"></i> Đơn hàng liên quan
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width: 180px;">Mã đơn hàng:</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $transaction->id_don_hang) }}"
                                        class="fw-bold text-primary text-decoration-none">
                                        #{{ $transaction->donHang->ma_don_hang ?? $transaction->id_don_hang }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tên khách hàng:</td>
                                <td class="fw-bold">
                                    {{ $transaction->donHang->ten_nguoi_nhan ?? ($transaction->donHang->nguoiDung->ho_ten ?? 'N/A') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Số điện thoại:</td>
                                <td>{{ $transaction->donHang->sdt_nguoi_nhan ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Địa chỉ giao:</td>
                                <td>{{ $transaction->donHang->dia_chi_giao_hang ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Trạng thái đơn:</td>
                                <td><span
                                        class="badge bg-info text-dark">{{ $transaction->donHang->trang_thai ?? 'N/A' }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
