@extends('admin.layouts.master')

@section('title', 'Chi tiết đơn hàng #' . ($order->ma_don_hang ?? $order->id) . ' - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Đơn hàng #{{ $order->ma_don_hang ?? $order->id }}</h3>
                <p class="text-muted mb-0">Thời gian đặt: {{ $order->ngay_tao }}</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- TIMELINE TIẾN TRÌNH ĐƠN HÀNG --}}
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

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-4">
                @if ($isCanceled)
                    <div class="alert alert-danger text-center mb-0" role="alert">
                        <i class="bi bi-x-circle-fill me-2 fs-5"></i> <strong>ĐƠN HÀNG NÀY ĐÃ BỊ HỦY</strong>
                    </div>
                @else
                    <div class="d-flex justify-content-between position-relative px-3 px-md-5">
                        @foreach ($statuses as $key => $info)
                            @php
                                $stepIndex = array_search($key, $statusKeys);
                                $isDone = $currentIndex !== false && $stepIndex <= $currentIndex;
                            @endphp
                            <div class="text-center position-relative" style="z-index: 2;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 {{ $isDone ? 'bg-success text-white' : 'bg-light text-muted border' }}"
                                    style="width: 48px; height: 48px; font-size: 1.25rem;">
                                    <i class="bi {{ $info['icon'] }}"></i>
                                </div>
                                <small
                                    class="fw-bold d-block {{ $isDone ? 'text-success' : 'text-muted' }}">{{ $info['title'] }}</small>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <!-- Thông tin sản phẩm -->
            <div class="col-md-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold">
                        <i class="bi bi-cart-check me-2"></i> Chi tiết sản phẩm đã đặt
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
                                    @forelse($order->chiTietDonHang ?? [] as $item)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $item->sach->anh_bia ? asset('storage/' . $item->sach->anh_bia) : asset('images/no-cover.jpg') }}"
                                                        class="rounded me-3 shadow-sm"
                                                        style="width: 40px; height: 55px; object-fit: cover;">
                                                    <div>
                                                        <div class="fw-bold">
                                                            {{ $item->sach->tieu_de ?? 'Sách không còn tồn tại' }}</div>
                                                        <small class="text-muted">Mã: #{{ $item->id_sach }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ number_format($item->don_gia ?? 0, 0, ',', '.') }} đ
                                            </td>
                                            <td class="text-center fw-bold">{{ $item->so_luong }}</td>
                                            <td class="text-end pe-3 fw-bold text-danger">
                                                {{ number_format(($item->don_gia ?? 0) * $item->so_luong, 0, ',', '.') }} đ
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-3 text-muted">Không có dữ liệu chi tiết
                                                sản phẩm.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Tổng tiền thanh toán:</td>
                                        <td class="text-end pe-3 fw-bold text-danger fs-5">
                                            {{ number_format($order->tong_tien ?? 0, 0, ',', '.') }} đ
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin khách hàng & cập nhật trạng thái -->
            <div class="col-md-4 mb-4">
                <!-- Cập nhật trạng thái -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-pencil-square me-2"></i> Cập nhật trạng thái đơn
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label fw-bold">Trạng thái hiện tại</label>
                                <select name="trang_thai" class="form-select">
                                    <option value="cho_xu_ly" {{ $order->trang_thai == 'cho_xu_ly' ? 'selected' : '' }}>Chờ
                                        xử lý</option>
                                    <option value="dang_xu_ly" {{ $order->trang_thai == 'dang_xu_ly' ? 'selected' : '' }}>
                                        Đang xử lý</option>
                                    <option value="dang_giao" {{ $order->trang_thai == 'dang_giao' ? 'selected' : '' }}>
                                        Đang giao hàng</option>
                                    <option value="hoan_thanh" {{ $order->trang_thai == 'hoan_thanh' ? 'selected' : '' }}>
                                        Hoàn thành</option>
                                    <option value="da_huy" {{ $order->trang_thai == 'da_huy' ? 'selected' : '' }}>Hủy đơn
                                        hàng</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-save me-1"></i> Lưu thay đổi
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Thông tin giao hàng & Thanh toán -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold">
                        <i class="bi bi-person-lines-fill me-2"></i> Thông tin giao hàng
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Người nhận:</strong>
                            {{ $order->ten_nguoi_nhan ?? ($order->nguoiDung->ho_ten ?? 'Khách lẻ') }}</p>
                        <p class="mb-2"><strong>Số điện thoại:</strong> {{ $order->sdt_nguoi_nhan ?? 'Chưa cập nhật' }}
                        </p>
                        <p class="mb-2"><strong>Địa chỉ nhận hàng:</strong>
                            {{ $order->dia_chi_giao_hang ?? 'Chưa cập nhật' }}</p>
                        <hr>
                        <p class="mb-2">
                            <strong>Phương thức thanh toán:</strong>
                            @php
                                $pt = strtolower(
                                    $order->thanhToan->phuong_thuc ?? ($order->phuong_thuc_thanh_toan ?? 'cod'),
                                );
                            @endphp
                            @if ($pt === 'momo')
                                <span class="badge bg-danger">MoMo</span>
                            @elseif($pt === 'vnpay')
                                <span class="badge bg-primary">VNPay</span>
                            @else
                                <span class="badge bg-secondary">COD (Tiền mặt)</span>
                            @endif
                        </p>
                        <p class="mb-0">
                            <strong>Trạng thái thanh toán:</strong>
                            @if (($order->thanhToan->trang_thai ?? '') === 'da_thanh_toan' || $order->trang_thai === 'hoan_thanh')
                                <span class="badge bg-success">Đã thanh toán</span>
                            @else
                                <span class="badge bg-warning text-dark">Chờ thanh toán</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
