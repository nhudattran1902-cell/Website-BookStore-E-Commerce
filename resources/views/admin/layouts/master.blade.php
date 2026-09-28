<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Tiêu đề động cho từng trang --}}
    <title>@yield('title', 'Quản trị BOOK & BOX')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('admin_assets/images/favicon.ico') }}">

    <!-- Local Third-Party Libraries CSS -->
    <link rel="stylesheet" href="{{ asset('admin_assets/libs/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/libs/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/libs/apexcharts/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/libs/flatpickr/flatpickr.min.css') }}">

    <!-- Main Design System & Custom Stylesheet -->
    <link rel="stylesheet" href="{{ asset('admin_assets/css/main.css') }}">

    {{-- Nơi nhúng CSS riêng của từng trang con --}}
    @stack('styles')
</head>

<body>

    <!-- 1. Nhúng Sidebar Component -->
    @include('admin.layouts.sidebar')

    <!-- Khối bao quanh nội dung chính -->
    <div class="main-wrapper">

        <!-- 2. Nhúng Top Navbar Header Component -->
        @include('admin.layouts.header')

        <!-- 3. Nơi hiển thị nội dung động của các trang CRUD (Dashboard, Books, Categories...) -->
        @yield('content')

        <!-- 4. Nhúng Footer Component -->
        @include('admin.layouts.footer')

    </div>

    <!-- Local Third-Party Libraries JS Dependencies -->
    <script src="{{ asset('admin_assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin_assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('admin_assets/libs/flatpickr/flatpickr.min.js') }}"></script>

    <!-- Local Dashboard Controller Script -->
    <script src="{{ asset('admin_assets/js/dashboard.js') }}"></script>

    {{-- Nơi nhúng JavaScript riêng của từng trang con --}}
    @stack('scripts')
</body>

</html>
