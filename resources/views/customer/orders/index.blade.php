@extends('layouts.app')

@section('title', 'Lịch sử đơn hàng - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Lịch Sử Đơn Hàng</h3>
                <p class="text-muted mb-0">Quản lý và theo dõi danh sách tất cả các đơn hàng bạn đã đặt</p>
            </div>
        </div>

        {{-- Thông báo Lỗi / Thành công --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4 rounded-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- BỘ LỌC TAB TRẠNG THÁI ĐƠN HÀNG --}}
        @php $currentStatus = request('status', 'all'); @endphp
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-2">
                <ul class="nav nav-pills nav-fill gap-2">
                    <li class="nav-item">
                        <a class="nav-link rounded-pill fw-medium {{ $currentStatus === 'all' ? 'active bg-dark text-white' : 'text-secondary' }}"
                            href="{{ route('customer.orders.index', ['status' => 'all']) }}">Tất cả</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill fw-medium {{ $currentStatus === 'cho_xu_ly' ? 'active bg-warning text-dark' : 'text-secondary' }}"
                            href="{{ route('customer.orders.index', ['status' => 'cho_xu_ly']) }}">Chờ xử lý</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill fw-medium {{ $currentStatus === 'dang_xu_ly' ? 'active bg-info text-dark' : 'text-secondary' }}"
                            href="{{ route('customer.orders.index', ['status' => 'dang_xu_ly']) }}">Đang xử lý</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill fw-medium {{ $currentStatus === 'dang_giao' ? 'active bg-primary text-white' : 'text-secondary' }}"
                            href="{{ route('customer.orders.index', ['status' => 'dang_giao']) }}">Đang giao</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill fw-medium {{ $currentStatus === 'hoan_thanh' ? 'active bg-success text-white' : 'text-secondary' }}"
                            href="{{ route('customer.orders.index', ['status' => 'hoan_thanh']) }}">Hoàn thành</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill fw-medium {{ $currentStatus === 'da_huy' ? 'active bg-danger text-white' : 'text-secondary' }}"
                            href="{{ route('customer.orders.index', ['status' => 'da_huy']) }}">Đã hủy</a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- DANH SÁCH ĐƠN HÀNG --}}
        @forelse($orders as $order)
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div
                    class="card-header bg-light py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="fw-bold text-dark me-2">Mã đơn: #{{ $order->ma_don_hang }}</span>
                        <small class="text-muted">| Ngày đặt: {{ $order->ngay_tao }}</small>
                    </div>
                    <div>
                        @switch($order->trang_thai)
                            @case('cho_xu_ly')
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Chờ xử lý</span>
                            @break

                            @case('dang_xu_ly')
                                <span class="badge bg-info text-dark"><i class="bi bi-box-seam me-1"></i>Đang xử lý</span>
                            @break

                            @case('dang_giao')
                                <span class="badge bg-primary"><i class="bi bi-truck me-1"></i>Đang giao hàng</span>
                            @break

                            @case('hoan_thanh')
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Hoàn thành</span>
                            @break

                            @case('da_huy')
                                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Đã hủy</span>
                            @break

                            @default
                                <span class="badge bg-secondary">{{ $order->trang_thai }}</span>
                        @endswitch
                    </div>
                </div>

                <div class="card-body p-4">
                    @foreach ($order->chiTietDonHang as $item)
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <img src="{{ $item->sach?->anh_bia_url ?: asset('images/no-cover.jpg') }}"
                                class="rounded-3 me-3" style="width: 55px; height: 75px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">{{ $item->sach->tieu_de ?? 'Sách đã xóa' }}</h6>
                                <small class="text-muted">Số lượng: {{ $item->so_luong }} x
                                    {{ number_format($item->don_gia, 0, ',', '.') }} đ</small>
                            </div>
                            <div class="fw-bold text-danger">
                                {{ number_format($item->thanh_tien, 0, ',', '.') }} đ
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex flex-wrap justify-content-between align-items-center pt-2">
                        <div>
                            <small class="text-muted">Phương thức thanh toán: </small>
                            <span
                                class="badge bg-light text-dark border me-2">{{ $order->thanhToan->phuong_thuc_thanh_toan ?? 'COD' }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                            <div>
                                <span class="text-muted small">Tổng tiền:</span>
                                <span
                                    class="fw-bold fs-5 text-danger ms-1 me-2">{{ number_format($order->thanh_tien, 0, ',', '.') }}
                                    đ</span>
                            </div>

                            {{-- Nút Hủy Đơn Hàng (Chỉ hiển thị khi Đơn ở trạng thái Chờ Xử Lý) --}}
                            @if ($order->trang_thai === 'cho_xu_ly')
                                <form action="{{ route('customer.orders.cancel', $order->id) }}" method="POST"
                                    onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');"
                                    class="m-0">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                        <i class="bi bi-x-circle me-1"></i> Hủy đơn
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('customer.orders.show', $order->id) }}"
                                class="btn btn-outline-dark btn-sm rounded-pill px-3">
                                <i class="bi bi-eye me-1"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                    <i class="bi bi-bag-x fs-1 text-muted mb-3 d-block"></i>
                    <h5>Bạn chưa có đơn hàng nào trong danh mục này!</h5>
                    <p class="text-muted">Hãy khám phá cửa hàng và chọn cho mình những cuốn sách yêu thích.</p>
                    <div>
                        <a href="{{ route('books.index') }}" class="btn btn-danger rounded-pill px-4">
                            <i class="bi bi-shop me-1"></i> Mua sắm ngay
                        </a>
                    </div>
                </div>
            @endforelse

            @if (method_exists($orders, 'links'))
                <div class="d-flex justify-content-end pt-3">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    @endsection
