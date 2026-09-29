<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- 1. Top Bar Header -->
<div class="top-bar py-2 bg-light border-bottom">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Trợ giúp & Hotline / Hệ thống cửa hàng -->
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('pages.contact') }}" class="top-bar-item text-decoration-none text-dark small">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                <span class="d-none d-sm-inline">Hệ thống cửa hàng</span>
            </a>
            <div class="text-muted small d-none d-md-block">
                <i class="bi bi-telephone-fill me-1"></i> Hotline: <span class="fw-bold text-dark">+84 767417206</span>
            </div>
        </div>

        <!-- Utility Links & Auth Section -->
        <div class="d-flex align-items-center gap-3">
            <!-- Tra cứu Đơn hàng -->
            <a href="{{ Auth::check() ? route('customer.orders.index') : route('login') }}"
                class="top-bar-item text-decoration-none text-dark small">
                <i class="bi bi-truck me-1"></i>
                <span class="d-none d-sm-inline">Tra cứu đơn hàng</span>
            </a>

            <!-- Yêu thích -->
            <a href="{{ route('books.index') }}" class="top-bar-item text-decoration-none text-dark small">
                <i class="bi bi-heart me-1"></i>
                <span class="d-none d-sm-inline">Yêu thích</span>
            </a>

            <!-- Giỏ hàng -->
            <a href="{{ route('cart.index') }}"
                class="top-bar-item text-decoration-none text-dark small position-relative">
                <i class="bi bi-bag me-1"></i>
                <span>Giỏ hàng</span>
            </a>

            <!-- User / Member Section -->
            @auth
                <!-- Đã đăng nhập: Click mở Popup Thông báo / Member -->
                <button type="button"
                    class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 ms-2 d-flex align-items-center gap-1"
                    data-bs-toggle="modal" data-bs-target="#smemberNotificationModal">
                    <i class="bi bi-person-circle fs-6"></i>
                    <span class="fw-bold">{{ Auth::user()->ho_ten ?? Auth::user()->ten_dang_nhap }}</span>
                    <i class="bi bi-bell-fill text-warning ms-1"></i>
                </button>
            @else
                <!-- Chưa đăng nhập -->
                <a href="{{ route('login') }}" class="btn btn-sm btn-dark rounded-pill px-3 py-1 ms-2">
                    <i class="bi bi-person me-1"></i> Đăng nhập
                </a>
            @endauth
        </div>
    </div>
</div>

<!-- 2. Header Navigation với Form tìm kiếm bo tròn chuẩn đẹp -->
<nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="navbar-brand d-flex align-items-center gap-2 me-4">
            <span class="fw-bold fs-4 text-dark">BOOK</span>
            <img src="{{ asset('images/logo.svg') }}" alt="BOOK & BOX" class="brand-logo" style="height: 35px;">
            <span class="fw-bold fs-4 text-danger">BOX</span>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
            data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Mở menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <!-- Menu Nav -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-medium">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active fw-bold text-danger' : '' }}"
                        href="{{ route('home') }}">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('books.index') ? 'active fw-bold text-danger' : '' }}"
                        href="{{ route('books.index') }}">Cửa hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pages.about') }}">Giới thiệu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pages.contact') }}">Liên hệ</a>
                </li>
            </ul>

            <!-- Thanh tìm kiếm tích hợp (Search Bar) -->
            <form action="{{ route('books.search') }}" method="GET" class="d-flex flex-grow-1 mx-lg-4 my-2 my-lg-0"
                style="max-width: 500px;">
                <div class="input-group shadow-sm rounded-pill overflow-hidden border w-100">
                    <input type="text" name="keyword" class="form-control border-0 px-3 py-2 shadow-none"
                        placeholder="Tìm kiếm sách, tác giả, thể loại..." value="{{ request('keyword') }}"
                        aria-label="Tìm kiếm sách" required>
                    <button class="btn btn-warning px-3 border-0 d-flex align-items-center justify-content-center"
                        type="submit" id="button-search">
                        <i class="bi bi-search fs-5 text-dark"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</nav>

<!-- 3. Popup Smember / Thông báo dành cho User đã đăng nhập -->
@auth
    <div class="modal fade" id="smemberNotificationModal" tabindex="-1" aria-labelledby="smemberNotificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <!-- Modal Header -->
                <div class="modal-header bg-danger text-white rounded-top-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge-fill fs-4"></i>
                        <div>
                            <h6 class="modal-title fw-bold mb-0" id="smemberNotificationModalLabel">
                                Xin chào, {{ Auth::user()->ho_ten ?? Auth::user()->ten_dang_nhap }}!
                            </h6>
                            <small class="opacity-75">Thành viên Smember BOOK & BOX</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body với Tabs -->
                <div class="modal-body p-0">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs nav-justified border-bottom" id="smemberTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold text-dark border-0 py-3" id="all-tab"
                                data-bs-toggle="tab" data-bs-target="#all-notifications" type="button" role="tab">
                                Tất cả
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-dark border-0 py-3" id="orders-tab" data-bs-toggle="tab"
                                data-bs-target="#orders-notifications" type="button" role="tab">
                                Đơn hàng
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-dark border-0 py-3" id="cskh-tab" data-bs-toggle="tab"
                                data-bs-target="#cskh-notifications" type="button" role="tab">
                                CSKH
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content p-3" id="smemberTabContent" style="max-height: 350px; overflow-y: auto;">
                        <!-- Tab Tất cả -->
                        <div class="tab-pane fade show active" id="all-notifications" role="tabpanel">
                            <div class="notification-item p-2 mb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-danger">Ưu đãi</span>
                                    <small class="text-muted">Hôm nay</small>
                                </div>
                                <p class="mb-0 small fw-bold">Mã giảm giá BOOKBOX10 đã có trong ví quà tặng của bạn!</p>
                            </div>
                            <div class="notification-item p-2 mb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-primary">Đơn hàng</span>
                                    <small class="text-muted">Vừa xong</small>
                                </div>
                                <p class="mb-0 small">Cảm ơn bạn đã đồng hành cùng hệ thống sách BOOK & BOX.</p>
                            </div>
                        </div>

                        <!-- Tab Đơn hàng -->
                        <div class="tab-pane fade" id="orders-notifications" role="tabpanel">
                            <div class="text-center py-4">
                                <i class="bi bi-box-seam fs-1 text-muted d-block mb-2"></i>
                                <a href="{{ route('customer.orders.index') }}" class="btn btn-sm btn-outline-danger">
                                    Xem lịch sử đơn hàng
                                </a>
                            </div>
                        </div>

                        <!-- Tab CSKH -->
                        <div class="tab-pane fade" id="cskh-notifications" role="tabpanel">
                            <div class="notification-item p-2 mb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-success">Hỗ trợ</span>
                                    <small class="text-muted">24/7</small>
                                </div>
                                <p class="mb-0 small">Nếu cần hỗ trợ gấp, vui lòng liên hệ hotline hoặc dùng Live Chat ở
                                    góc màn hình.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-light rounded-bottom-4">
                    <a href="{{ route('customer.profile') }}" class="btn btn-sm btn-outline-secondary me-auto">
                        <i class="bi bi-gear me-1"></i> Hồ sơ
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
@endauth
