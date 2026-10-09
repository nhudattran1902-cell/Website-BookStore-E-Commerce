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
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                {{ session('error') }}
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
            $nextStatuses = [
                'cho_xu_ly' => ['dang_xu_ly', 'da_huy'],
                'dang_xu_ly' => ['dang_giao', 'da_huy'],
                'dang_giao' => ['hoan_thanh'],
                'hoan_thanh' => [],
                'da_huy' => [],
            ][$order->trang_thai] ?? [];
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
                                                    <img src="{{ $item->sach?->anh_bia_url ?: asset('images/no-cover.jpg') }}"
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
                                        <td colspan="3" class="text-end">Tạm tính:</td>
                                        <td class="text-end pe-3">
                                            {{ number_format($order->tong_tien ?? 0, 0, ',', '.') }} đ
                                        </td>
                                    </tr>
                                    @if (($order->so_tien_giam_gia ?? 0) > 0)
                                        <tr>
                                            <td colspan="3" class="text-end text-success">
                                                Giảm giá{{ $order->maGiamGia?->ma_code ? ' ('.$order->maGiamGia->ma_code.')' : '' }}:
                                            </td>
                                            <td class="text-end pe-3 text-success">
                                                -{{ number_format($order->so_tien_giam_gia, 0, ',', '.') }} đ
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Khách cần thanh toán:</td>
                                        <td class="text-end pe-3 fw-bold text-danger fs-5">
                                            {{ number_format($order->thanh_tien ?? $order->thanhToan?->so_tien ?? $order->tong_tien ?? 0, 0, ',', '.') }} đ
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
                @if (auth()->user()->hasPermission('orders.status.update'))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-pencil-square me-2"></i> Cập nhật trạng thái đơn
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label fw-bold">Chuyển trạng thái từ {{ $order->trang_thai }}</label>
                                <select name="trang_thai" class="form-select">
                                    @foreach ($nextStatuses as $nextStatus)
                                        <option value="{{ $nextStatus }}">{{ str_replace('_', ' ', ucfirst($nextStatus)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ghi chú</label>
                                <textarea name="ghi_chu" class="form-control" maxlength="500" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-save me-1"></i> Lưu thay đổi
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-dark text-white fw-bold">Thông tin vận chuyển</div>
                    <div class="card-body">
                        @if (auth()->user()->hasPermission('orders.shipping.update') && in_array($order->trang_thai, ['dang_xu_ly', 'dang_giao'], true))
                            <form action="{{ route('admin.orders.updateShipping', $order->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label">Đơn vị</label>
                                    <select name="don_vi_van_chuyen" class="form-select" required>
                                        <option value="">Chọn hãng vận chuyển</option>
                                        <option value="GHN" @selected($order->don_vi_van_chuyen === 'GHN')>GHN</option>
                                        <option value="GHTK" @selected($order->don_vi_van_chuyen === 'GHTK')>GHTK</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mã vận đơn</label>
                                    <input name="ma_van_don" class="form-control" value="{{ $order->ma_van_don }}" maxlength="100" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Link theo dõi</label>
                                    <input name="tracking_url" type="url" class="form-control" value="{{ $order->tracking_url }}" maxlength="500">
                                </div>
                                <button type="submit" class="btn btn-outline-primary w-100">Lưu vận đơn</button>
                            </form>
                        @else
                            <p class="mb-1"><strong>Đơn vị:</strong> {{ $order->don_vi_van_chuyen ?: 'Chưa tạo vận đơn' }}</p>
                            <p class="mb-1"><strong>Mã vận đơn:</strong> {{ $order->ma_van_don ?: 'Chưa có' }}</p>
                            @if ($order->tracking_url)
                                <a href="{{ $order->tracking_url }}" target="_blank" rel="noopener noreferrer">Mở tracking</a>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Thông tin giao hàng -->
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
                            {{ $order->dia_chi_nhan ?: ($order->dia_chi_giao_hang ?? 'Chưa cập nhật') }}</p>
                    </div>
                </div>

                @if (auth()->user()->hasPermission('payments.view') && $order->thanhToan)
                    @php
                        $paymentMethod = $order->thanhToan->phuong_thuc_thanh_toan;
                        $paymentMethods = [
                            'COD' => 'COD · Thu hộ khi giao hàng',
                            'MoMo' => 'Ví MoMo',
                            'VNPay' => 'VNPay',
                            'BankTransfer' => 'Chuyển khoản VietQR',
                        ];
                        $paymentStatuses = [
                            'da_thanh_toan' => ['Đã thanh toán', 'success'],
                            'cho_thanh_toan' => ['Chờ thanh toán', 'warning text-dark'],
                            'that_bai' => ['Thất bại', 'danger'],
                            'da_huy' => ['Đã hủy', 'secondary'],
                            'hoan_tien' => ['Đã hoàn tiền', 'info text-dark'],
                        ];
                        $paymentStatus = $paymentStatuses[$order->thanhToan->trang_thai]
                            ?? [ucfirst(str_replace('_', ' ', $order->thanhToan->trang_thai)), 'secondary'];
                    @endphp
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                            <div>
                                <div class="fw-bold mb-2"><i class="bi bi-credit-card me-2"></i>Tóm tắt thanh toán</div>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span>{{ $paymentMethods[$paymentMethod] ?? $paymentMethod }}</span>
                                    <span class="badge bg-{{ $paymentStatus[1] }}">{{ $paymentStatus[0] }}</span>
                                    <span class="text-muted">{{ number_format($order->thanhToan->so_tien ?? 0, 0, ',', '.') }} đ</span>
                                </div>
                            </div>
                            <a href="{{ route('admin.transactions.show', $order->thanhToan->id) }}" class="btn btn-outline-primary btn-sm">
                                Xem giao dịch thanh toán <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if ($order->lichSuDonHang->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light fw-bold">Nhật ký đơn hàng</div>
                <div class="card-body">
                    @foreach ($order->lichSuDonHang as $event)
                        <div class="border-start border-2 ps-3 pb-3 ms-1">
                            <div class="fw-semibold">{{ $event->ghi_chu ?: $event->trang_thai_moi }}</div>
                            <small class="text-muted">
                                {{ $event->ngay_tao }}
                                @if ($event->vi_tri) · {{ $event->vi_tri }} @endif
                                @if ($event->nguoiThayDoi) · {{ $event->nguoiThayDoi->ho_ten }} @endif
                            </small>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
