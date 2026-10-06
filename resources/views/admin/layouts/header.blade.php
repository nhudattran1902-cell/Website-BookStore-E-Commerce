<header class="navbar-custom">
    <div class="navbar-left">
        <!-- Desktop sidebar toggle -->
        <button class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center me-3"
            id="desktop-sidebar-toggle" aria-label="Thu nhỏ thanh bên">
            <i class="bi bi-chevron-bar-left"></i>
        </button>
        <!-- Mobile sidebar toggle -->
        <button class="sidebar-toggle-btn me-2" id="sidebar-toggle" aria-label="Mở menu">
            <i class="bi bi-list"></i>
        </button>

        <!-- Quick Actions Dropdown -->
        <div class="dropdown ms-2">
            <button class="btn-quick-action dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                id="quick-actions-dropdown">
                <i class="bi bi-plus-lg"></i>
                <span>Tạo mới</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-quick-action" aria-labelledby="quick-actions-dropdown">
                <li class="dropdown-header">Tác vụ nhanh</li>
                <li><a class="dropdown-item" href="{{ route('admin.books.create') }}"><i class="bi bi-book me-2"></i> Thêm sách mới</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.categories.index') }}"><i class="bi bi-tags me-2"></i> Thêm thể loại</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.authors.index') }}"><i class="bi bi-person-badge me-2"></i> Thêm tác giả</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.publishers.index') }}"><i class="bi bi-building me-2"></i> Thêm nhà xuất bản</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}"><i class="bi bi-cart-check me-2"></i> Xem đơn hàng</a></li>
            </ul>
        </div>
    </div>

    <!-- Mid navbar: search pill -->
    <div class="navbar-search-wrapper">
        <form action="{{ route('books.search') }}" method="GET" class="d-flex w-100" target="_blank">
            <input type="text" name="q" class="navbar-search-input" placeholder="Tìm sách trên website..." id="main-search">
            <button class="navbar-search-btn" type="submit" aria-label="Tìm kiếm">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>

    <!-- Right actions -->
    <div class="navbar-actions">
        <!-- Fullscreen Toggle -->
        <button class="navbar-action-btn me-1" aria-label="Toàn màn hình" id="btn-fullscreen">
            <i class="bi bi-arrows-fullscreen"></i>
        </button>

        <!-- Notifications -->
        <div class="dropdown">
            <button class="navbar-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
                aria-expanded="false" id="btn-notifications" data-bs-auto-close="outside">
                <i class="bi bi-bell"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-notification p-0"
                aria-labelledby="btn-notifications">
                <div class="notification-header">
                    <h6 class="notification-title">Thông báo</h6>
                </div>
                <div class="notification-list p-2">
                    @forelse ($paymentNotifications as $paymentNotification)
                        <a href="{{ route('admin.orders.show', $paymentNotification->data['order_id']) }}" class="notification-item text-decoration-none">
                            <div class="notification-icon bg-success text-white">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                            <div class="notification-content">
                                <p class="notification-text">{{ $paymentNotification->data['message'] }}</p>
                                <span class="notification-time">{{ $paymentNotification->created_at->diffForHumans() }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-muted small text-center mb-0 py-3">Chưa có thông báo thanh toán.</p>
                    @endforelse
                </div>
                <a href="{{ route('admin.orders.index') }}" class="notification-footer">Xem tất cả đơn hàng</a>
            </div>
        </div>

        <!-- Profile Dropdown -->
        @php $adminUser = Auth::user(); @endphp
        <div class="dropdown ms-2">
            <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
                aria-expanded="false" id="profile-dropdown">
                <img src="{{ $adminUser?->anh_dai_dien_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($adminUser->ho_ten ?? 'Admin') . '&background=1b4332&color=b4f105&size=32' }}"
                    alt="Profile" class="navbar-profile-img">
                <span class="navbar-profile-name d-none d-md-inline">{{ $adminUser->ho_ten ?? 'Quản trị viên' }}</span>
                <i class="bi bi-chevron-down navbar-profile-caret"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profile-dropdown">
                <li class="dropdown-header">Xin chào, {{ $adminUser->ho_ten ?? 'Admin' }}!</li>
                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid me-2"></i> Bảng điều khiển</a></li>
                <li><a class="dropdown-item" href="{{ route('home') }}" target="_blank"><i class="bi bi-house me-2"></i> Xem website</a></li>
                <li><a class="dropdown-item" href="{{ route('customer.profile') }}"><i class="bi bi-person me-2"></i> Hồ sơ cá nhân</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>