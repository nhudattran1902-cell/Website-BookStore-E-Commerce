<!-- 1. Top Bar -->
<div class="container border-bottom py-2 d-none d-lg-block">
    <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            <i class="far fa-question-circle"></i> Bạn cần giúp đỡ ?
            <span class="ms-3"><i class="fas fa-phone-alt"></i> +84 767417206</span>
        </div>
        <div class="text-muted small">
            <i class="fas fa-map-marker-alt mx-2"></i>
            <i class="fas fa-truck mx-2"></i>
            <i class="far fa-heart mx-2"></i>
            <i class="far fa-user mx-2"></i>
            <i class="fas fa-shopping-bag mx-2"></i>
        </div>
    </div>
</div>

<!-- 2. Header Navigation -->



<nav class="navbar navbar-expand-lg navbar-light bg-white py-3">
    <div class="container">
        <a href="{{ route('home') }}" class="navbar-brand d-flex align-items-center gap-2">
            <span class="fw-bold fs-4">BOOK</span>
            <img src="{{ asset('images/logo.svg') }}" alt="BOOK & BOX" class="brand-logo">
            <span class="fw-bold fs-4">BOX</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Mở menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-medium">
                <li class="nav-item dropdown">
                    <a class="nav-link active" href="{{ route('home') }}">Trang chủ</a>
                </li>
                <li class="nav-item border-bottom border-danger border-2">
                    <a class="nav-link text-dark" href="{{ route('books.index') }}">Danh mục</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link" href="{{ route('books.index') }}">Cửa hàng</a>
                </li>
            </ul>
            <form action="{{ route('books.search') }}" method="GET" class="d-flex flex-grow-1 flex-lg-grow-0">
                <input type="text" name="q" class="form-control me-2" placeholder="Tìm kiếm sách, thể loại..."
                    value="{{ request('q') }}" required>
                <button type="submit" class="btn btn-outline-dark"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>
</nav>
