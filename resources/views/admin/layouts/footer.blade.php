<!-- START: Footer Component -->
<footer class="footer-custom">
    <div class="footer-left">
        <span class="footer-logo">
            <i class="bi bi-book-half"></i> BOOK & BOX Admin
        </span>
        <span class="footer-separator">|</span>
        <span class="footer-copy">&copy; {{ date('Y') }} Bản quyền thuộc về BOOK & BOX.</span>
    </div>
    <div class="footer-right">
        <ul class="footer-links">
            <li><a href="{{ route('admin.dashboard') }}" class="footer-link">Dashboard</a></li>
            <li><a href="{{ route('admin.books.index') }}" class="footer-link">Sách</a></li>
            <li><a href="{{ route('admin.orders.index') }}" class="footer-link">Đơn hàng</a></li>
            <li><a href="{{ route('home') }}" target="_blank" class="footer-link">Cửa hàng <span class="status-dot"></span></a></li>
        </ul>
    </div>
</footer>
<!-- END: Footer Component -->
