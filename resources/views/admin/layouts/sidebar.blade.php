@php
    $adminUser = Auth::user();
    $adminRoles = $adminUser?->vaiTro->pluck('ten_vai_tro') ?? collect();
    $isSuperAdmin = $adminRoles->contains('admin');
    $staffHome = route('admin.dashboard');
    if (! $isSuperAdmin) {
        if ($adminUser->hasPermission('chat.view')) {
            $staffHome = route('admin.chat.index');
        } elseif ($adminUser->hasPermission('reviews.view')) {
            $staffHome = route('admin.reviews.index');
        } elseif ($adminUser->hasPermission('inventory.view')) {
            $staffHome = route('admin.inventory.index');
        } elseif ($adminUser->hasPermission('reports.revenue.view')) {
            $staffHome = route('admin.reports.revenue');
        } elseif ($adminUser->hasPermission('orders.view')) {
            $staffHome = route('admin.orders.index');
        } elseif ($adminUser->hasPermission('payments.view')) {
            $staffHome = route('admin.transactions.index');
        } elseif ($adminUser->hasPermission('catalog.manage')) {
            $staffHome = route('admin.books.index');
        } else {
            $staffHome = route('admin.discount-codes.index');
        }
    }
@endphp

<div class="sidebar-wrapper" id="sidebar">
    <!-- Brand Logo / Identity -->
    <a href="{{ $staffHome }}" class="sidebar-brand">
        <i class="bi bi-book-half"></i>
        <span>BOOK & BOX</span>
    </a>

    <!-- Navigation Menu -->
    <div class="flex-grow-1 overflow-y-auto">
        <!-- Group: Tổng quan -->
        <div class="sidebar-menu-section">
            <div class="sidebar-menu-title">Tổng quan</div>
            <ul class="sidebar-menu-list">
                @if ($isSuperAdmin)
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        title="Dashboard">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>

        <!-- Group: Quản lý cửa hàng -->
        <div class="sidebar-menu-section">
            <div class="sidebar-menu-title">Quản lý cửa hàng</div>
            <ul class="sidebar-menu-list">
                @if ($adminUser->hasPermission('catalog.manage'))
                    <li class="sidebar-menu-item">
                        <a href="{{ route('admin.books.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.books.*') ? 'active' : '' }}"
                        title="Quản lý Sách">
                        <i class="bi bi-book"></i>
                        <span>Quản lý Sách</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.categories.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                        title="Thể loại">
                        <i class="bi bi-tags"></i>
                        <span>Thể loại</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.authors.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.authors.*') ? 'active' : '' }}"
                        title="Tác giả">
                        <i class="bi bi-person-badge"></i>
                        <span>Tác giả</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.publishers.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.publishers.*') ? 'active' : '' }}"
                        title="Nhà xuất bản">
                        <i class="bi bi-building"></i>
                        <span>Nhà xuất bản</span>
                    </a>
                </li>
                @endif
                @if ($adminUser->hasPermission('inventory.view'))
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.inventory.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}"
                        title="Kho hàng">
                        <i class="bi bi-boxes"></i>
                        <span>Kho hàng</span>
                    </a>
                </li>
                @endif
                @if ($adminUser->hasPermission('discounts.manage'))
                    <li class="sidebar-menu-item">
                        <a href="{{ route('admin.discount-codes.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.discount-codes.*') ? 'active' : '' }}"
                        title="Mã giảm giá">
                        <i class="bi bi-ticket-perforated"></i>
                        <span>Mã giảm giá</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>

        <!-- Group: Bán hàng & Khách hàng -->
        <div class="sidebar-menu-section">
            <div class="sidebar-menu-title">Bán hàng & Người dùng</div>
            <ul class="sidebar-menu-list">
                @if ($adminUser->hasPermission('orders.view'))
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.orders.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                        title="Đơn hàng">
                        <i class="bi bi-cart-check"></i>
                        <span>Đơn hàng</span>
                    </a>
                </li>
                @endif
                @if ($adminUser->hasPermission('reports.revenue.view'))
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.reports.revenue') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                        title="Báo cáo doanh thu">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>Báo cáo doanh thu</span>
                    </a>
                </li>
                @endif
                @if ($isSuperAdmin)
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.users.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                        title="Khách hàng">
                        <i class="bi bi-people"></i>
                        <span>Người dùng</span>
                    </a>
                </li>
                @endif
                @if ($isSuperAdmin)
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.login-logs.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.login-logs.*') ? 'active' : '' }}"
                        title="Nhật ký đăng nhập admin">
                        <i class="bi bi-shield-lock"></i>
                        <span>Nhật ký đăng nhập</span>
                    </a>
                </li>
                @endif
                @if ($isSuperAdmin)
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.action-logs.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.action-logs.*') ? 'active' : '' }}"
                        title="Nhật ký thao tác">
                        <i class="bi bi-clipboard-data"></i>
                        <span>Nhật ký thao tác</span>
                    </a>
                </li>
                @endif
                @if ($adminUser->hasPermission('chat.view'))
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.chat.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.chat.*') ? 'active' : '' }}"
                        title="Hỗ trợ CSKH">
                        <i class="bi bi-chat-dots"></i>
                        <span>Hỗ trợ CSKH</span>
                    </a>
                </li>
                @endif
                @if ($adminUser->hasPermission('reviews.view'))
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.reviews.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}"
                        title="Đánh giá">
                        <i class="bi bi-star"></i>
                        <span>Đánh giá</span>
                    </a>
                </li>
                @endif
                @if ($adminUser->hasPermission('payments.view'))
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.transactions.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}"
                        title="Giao dịch thanh toán">
                        <i class="bi bi-credit-card"></i>
                        <span>Giao dịch thanh toán</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>

        <!-- Group: Website -->
        <div class="sidebar-menu-section">
            <div class="sidebar-menu-title">Cửa hàng trực tuyến</div>
            <ul class="sidebar-menu-list">
                <li class="sidebar-menu-item">
                    <a href="{{ route('home') }}" target="_blank" class="sidebar-menu-link" title="Xem website">
                        <i class="bi bi-box-arrow-up-right"></i>
                        <span>Xem website</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Sidebar Profile Card -->
    <div class="sidebar-profile">
        <img src="{{ $adminUser?->anh_dai_dien_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($adminUser->ho_ten ?? 'Admin') . '&background=1b4332&color=b4f105&size=48' }}"
            alt="{{ $adminUser->ho_ten ?? 'Admin' }}" class="sidebar-profile-img">
        <div class="sidebar-profile-info">
            <div class="sidebar-profile-name">{{ $adminUser->ho_ten ?? 'Quản trị viên' }}</div>
            <div class="sidebar-profile-email">{{ $adminUser->email ?? 'admin@bookbox.com' }}</div>
        </div>
    </div>
</div>
