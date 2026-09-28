@extends('admin.layouts.master')

@section('title', 'Dashboard - Quản trị BOOK & BOX')

@section('content')
    <!-- START: Dashboard Header Banner -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Tổng quan tình hình kinh doanh và quản lý cửa hàng sách BOOK & BOX.</p>
        </div>
        <button class="btn-date-picker" type="button" id="date-picker-trigger">
            <i class="bi bi-calendar4-event"></i>
            <span id="selected-date-range">Hôm nay: {{ date('d/m/Y') }}</span>
        </button>
    </div>
    <!-- END: Dashboard Header Banner -->

    <!-- START: Main Layout Grid -->
    <div class="row g-4">

        <!-- TOP AREA: Quick Info Stat Cards Row -->
        <div class="col-12">
            <div class="row g-4">
                <!-- Stat Card 1: Green Alert Banner -->
                <div class="col-md-4">
                    <div class="card alert-green-card h-100">
                        <div class="position-relative z-index-2">
                            <span class="alert-green-badge">Đơn hàng mới</span>
                            <div class="alert-green-date">{{ date('d/m/Y') }}</div>
                            <div class="alert-green-text">
                                Có <strong>{{ $donHangMoi }}</strong> đơn hàng đang chờ xử lý
                            </div>
                        </div>
                        <a href="{{ route('admin.orders.index') }}" class="alert-green-link z-index-2" id="alert-link-statistics">
                            <span>Xử lý đơn hàng ngay</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>

                        <!-- Inline SVG geometric decoration -->
                        <svg class="alert-green-bg-shape" viewBox="0 0 100 100" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <g transform="translate(50,50)">
                                <rect x="-6" y="-45" width="12" height="90" rx="6" ry="6" fill="#B4F105" />
                                <rect x="-6" y="-45" width="12" height="90" rx="6" ry="6" fill="#B4F105" transform="rotate(60)" />
                                <rect x="-6" y="-45" width="12" height="90" rx="6" ry="6" fill="#B4F105" transform="rotate(120)" />
                            </g>
                        </svg>
                    </div>
                </div>

                <!-- Stat Card 2: Doanh thu thuần -->
                <div class="col-md-4">
                    <div class="card card-stat d-flex flex-column justify-content-between h-100">
                        <div>
                            <div class="card-header">
                                <span class="stat-label">Tổng Doanh Thu Hoàn Thành</span>
                                <div class="dropdown">
                                    <button class="card-more-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                        <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}"><i class="bi bi-list"></i> Xem đơn hàng</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="stat-value">{{ number_format($tongDoanhThu, 0, ',', '.') }} đ</div>
                            <div class="trend-badge trend-up">
                                <i class="bi bi-check-circle"></i>
                                <span>Doanh thu từ các đơn thành công</span>
                            </div>
                        </div>
                        <div class="sparkline-container sparkline-card-footer">
                            <div id="income-sparkline"></div>
                        </div>
                    </div>
                </div>

                <!-- Stat Card 3: Tồn kho & Khách hàng -->
                <div class="col-md-4">
                    <div class="card card-stat d-flex flex-column justify-content-between h-100">
                        <div>
                            <div class="card-header">
                                <span class="stat-label">Sách Tồn Kho & Đầu Sách</span>
                                <div class="dropdown">
                                    <button class="card-more-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                        <li><a class="dropdown-item" href="{{ route('admin.inventory.index') }}"><i class="bi bi-boxes"></i> Quản lý kho</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="stat-value">{{ number_format($tongSachTonKho, 0, ',', '.') }} cuốn</div>
                            <div class="trend-badge trend-up">
                                <i class="bi bi-book"></i>
                                <span>{{ $tongDauSach }} đầu sách • {{ $tongKhachHang }} khách hàng</span>
                            </div>
                        </div>
                        <div class="sparkline-container sparkline-card-footer">
                            <div id="return-sparkline"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: TOP AREA -->

        <!-- LEFT AREA: Primary Dashboard Stats & Tables -->
        <div class="col-xl-9 col-lg-8">
            <div class="row g-4">
                <!-- Column: Biểu đồ doanh thu -->
                <div class="col-12">
                    <div class="card mb-0">
                        <div class="card-header mb-2">
                            <h2 class="card-title">Biểu Đồ Doanh Thu Bán Sách</h2>
                            <div class="d-flex gap-3 align-items-center">
                                <div class="chart-legend-item">
                                    <span class="legend-dot bg-forest-medium"></span>
                                    <span class="chart-legend-label">Doanh thu</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-3">
                            <span class="stat-value-amount">{{ number_format($tongDoanhThu, 0, ',', '.') }} đ</span>
                            <span class="trend-badge trend-up fs-xs">Tổng tích lũy</span>
                        </div>
                        <div id="revenue-chart"></div>
                    </div>
                </div>

                <!-- Column: Đơn hàng & Giao dịch mới (Dữ liệu thật từ donHangGanDay) -->
                <div class="col-md-7 d-flex flex-column">
                    <div class="card h-100 flex-grow-1">
                        <div class="card-header">
                            <h2 class="card-title">Đơn Hàng Gần Đây</h2>
                            <div class="dropdown">
                                <button class="card-more-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                    <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}"><i class="bi bi-list"></i> Xem tất cả</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Transaction Items List Dữ liệu thật -->
                        <div class="transaction-list">
                            @forelse ($donHangGanDay as $dh)
                                <div class="transaction-item">
                                    <div class="transaction-icon bg-forest-light text-lime">
                                        <i class="bi bi-cart-check"></i>
                                    </div>
                                    <div class="transaction-info">
                                        <div class="transaction-name">
                                            <a href="{{ route('admin.orders.show', $dh->id) }}" class="text-decoration-none text-dark fw-bold">
                                                #{{ $dh->ma_don_hang }}
                                            </a>
                                            <span class="text-muted small ms-1">({{ $dh->nguoiDung->ho_ten ?? 'Khách' }})</span>
                                        </div>
                                        <div class="transaction-date">
                                            {{ $dh->ngay_tao ? \Carbon\Carbon::parse($dh->ngay_tao)->format('d/m/Y • H:i') : '' }}
                                            —
                                            @switch($dh->trang_thai)
                                                @case('cho_xu_ly') <span class="badge bg-warning text-dark">Chờ xử lý</span> @break
                                                @case('dang_xu_ly') <span class="badge bg-info text-dark">Đang xử lý</span> @break
                                                @case('dang_giao') <span class="badge bg-primary">Đang giao</span> @break
                                                @case('hoan_thanh') <span class="badge bg-success">Hoàn thành</span> @break
                                                @case('da_huy') <span class="badge bg-danger">Đã hủy</span> @break
                                            @endswitch
                                        </div>
                                    </div>
                                    <div class="transaction-amount text-success fw-bold">
                                        {{ number_format($dh->thanh_tien, 0, ',', '.') }} đ
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted">Chưa có giao dịch đơn hàng nào.</div>
                            @endforelse
                        </div>

                    </div>
                </div>

                <!-- Column: Thống kê Kho & Sản phẩm -->
                <div class="col-md-5 d-flex flex-column">
                    <div class="card h-100 flex-grow-1">
                        <div class="card-header">
                            <h2 class="card-title">Tình Trạng Kho Sách & Cửa Hàng</h2>
                            <div class="dropdown">
                                <button class="card-more-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                                    <li><a class="dropdown-item" href="{{ route('admin.books.create') }}"><i class="bi bi-plus-lg"></i> Thêm sách mới</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.inventory.index') }}"><i class="bi bi-boxes"></i> Quản lý kho</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="progress-container">
                            <div class="progress-label-row">
                                <span class="progress-label">Tổng đầu sách</span>
                                <span class="progress-value fw-bold">{{ $tongDauSach }}</span>
                            </div>
                            <div class="progress" role="progressbar">
                                <div class="progress-bar bg-lime-accent w-75"></div>
                            </div>
                        </div>

                        <div class="progress-container">
                            <div class="progress-label-row">
                                <span class="progress-label">Tổng sách tồn trong kho</span>
                                <span class="progress-value fw-bold">{{ number_format($tongSachTonKho, 0, ',', '.') }}</span>
                            </div>
                            <div class="progress" role="progressbar">
                                <div class="progress-bar bg-lime-accent w-65"></div>
                            </div>
                        </div>

                        <div class="progress-container">
                            <div class="progress-label-row">
                                <span class="progress-label">Đơn hàng chờ xử lý</span>
                                <span class="progress-value fw-bold text-danger">{{ $donHangMoi }}</span>
                            </div>
                            <div class="progress" role="progressbar">
                                <div class="progress-bar bg-brand-orange w-50"></div>
                            </div>
                        </div>

                        <div class="progress-container">
                            <div class="progress-label-row">
                                <span class="progress-label">Người dùng đăng ký</span>
                                <span class="progress-value fw-bold">{{ $tongKhachHang }}</span>
                            </div>
                            <div class="progress" role="progressbar">
                                <div class="progress-bar bg-lime-accent opacity-50 w-60"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- END: LEFT AREA -->

        <!-- RIGHT AREA: Performance Details Sidebar Panel -->
        <div class="col-xl-3 col-lg-4">
            <div class="right-panel-wrapper d-flex flex-column gap-4 h-100">

                <!-- Performance Donut Chart card -->
                <div class="card flex-grow-1 d-flex flex-column justify-content-between mb-0">
                    <div class="card-header mb-1">
                        <h2 class="card-title">Tỷ Lệ Tương Tác Sách</h2>
                    </div>

                    <div id="views-chart"></div>

                    <div class="chart-legends-container">
                        <div class="chart-legend-item">
                            <span class="legend-dot bg-lime-accent"></span>
                            <span class="text-muted-green">Xem chi tiết</span>
                        </div>
                        <div class="chart-legend-item">
                            <span class="legend-dot bg-forest-medium"></span>
                            <span class="text-muted-green">Thêm giỏ hàng</span>
                        </div>
                        <div class="chart-legend-item">
                            <span class="legend-dot bg-brand-orange"></span>
                            <span class="text-muted-green">Đã đặt hàng</span>
                        </div>
                    </div>
                </div>

                <!-- Level Up Promotion CTA banner -->
                <div class="promo-banner-card">
                    <svg class="promo-banner-bg-shape" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g transform="translate(50,50)">
                            <rect x="-6" y="-45" width="12" height="90" rx="6" ry="6" fill="#B4F105" />
                            <rect x="-6" y="-45" width="12" height="90" rx="6" ry="6" fill="#B4F105" transform="rotate(60)" />
                            <rect x="-6" y="-45" width="12" height="90" rx="6" ry="6" fill="#B4F105" transform="rotate(120)" />
                        </g>
                    </svg>

                    <h3 class="promo-title">Hệ Thống Bán Sách BOOK & BOX</h3>
                    <p class="promo-desc">Quản lý kho hàng, đơn hàng và danh mục trực quan, chính xác.</p>
                    <a href="{{ route('home') }}" target="_blank"
                        class="btn btn-promo w-100 text-center text-decoration-none">Xem Storefront</a>
                </div>
            </div>
        </div>
        <!-- END: RIGHT AREA -->

    </div>
    <!-- END: Main Layout Grid -->
@endsection
