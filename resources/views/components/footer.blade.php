<footer class="bg-white pt-5 border-top">
    <div class="container pb-5">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="container ps-0">
                    <div class="d-flex align-items-center mb-3">
                        <span class="fw-bold fs-4">BOOK</span>
                        <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="w-50 mx-2" style="max-height: 40px;">
                        <span class="fw-bold fs-4">BOX</span>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    12 Trịnh Đình Thảo, Phường Tân Phú<br />Thành Phố Hồ Chí Minh
                </p>
                <p class="mb-1 small fw-medium">
                    <i class="bi bi-envelope me-1"></i> book&box@gmail.com
                </p>
                <p class="small fw-medium">
                    <i class="bi bi-telephone me-1"></i> +84 767417206
                </p>
            </div>
            <div class="col-md-3">
                <h6 class="fw-bold mb-4">Khám phá</h6>
                <ul class="list-unstyled small text-muted">
                    <li class="mb-2">
                        <a href="{{ route('pages.about') }}" class="text-muted text-decoration-none">Về chúng tôi</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('books.index') }}" class="text-muted text-decoration-none">Cửa hàng sách</a>
                    </li>
                    <li class="mb-2">
                        <a href="https://maps.google.com/?q=12+Trịnh+Đình+Thảo+Tân+Phú+Hồ+Chí+Minh" target="_blank" class="text-muted text-decoration-none">Bản đồ chỉ đường</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('pages.contact') }}" class="text-muted text-decoration-none">Liên hệ hợp tác</a>
                    </li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="fw-bold mb-4">Hỗ trợ khách hàng</h6>
                <ul class="list-unstyled small text-muted">
                    <li class="mb-2">
                        <a href="{{ route('pages.faq') }}" class="text-muted text-decoration-none">Câu hỏi thường gặp (FAQ)</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('pages.shipping') }}" class="text-muted text-decoration-none">Chính sách vận chuyển</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('pages.return-policy') }}" class="text-muted text-decoration-none">Chính sách đổi trả</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('customer.orders.index') }}" class="text-muted text-decoration-none">Tra cứu đơn hàng</a>
                    </li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="fw-bold mb-4">Chính sách & Quy định</h6>
                <ul class="list-unstyled small text-muted">
                    <li class="mb-2">
                        <a href="{{ route('pages.privacy') }}" class="text-muted text-decoration-none">Chính sách bảo mật</a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('pages.terms') }}" class="text-muted text-decoration-none">Điều khoản sử dụng</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="border-top py-3 text-center text-muted small bg-light">
        <div class="container">
            &copy; {{ date('Y') }} BOOK & BOX. Bản quyền thuộc về BOOK & BOX.
        </div>
    </div>
</footer>
