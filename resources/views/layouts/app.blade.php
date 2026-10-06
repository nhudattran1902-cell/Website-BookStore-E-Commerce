<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Bookworm')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>

<body class="literary-site">
    @include('components.header')
    <div id="cart-toast-region" class="cart-toast-region position-fixed top-0 end-0 p-3" aria-live="polite"
        aria-atomic="true"></div>

    @yield('content')

    @include('components.footer')

    {{-- Popup Khuyến Mãi Trang Chủ --}}


    @if (request()->routeIs('home'))
        @include('components.promo-modal')
    @endif

    @include('components.chat-widget')

    <!-- Bootstrap 5 JavaScript Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>

</html>
