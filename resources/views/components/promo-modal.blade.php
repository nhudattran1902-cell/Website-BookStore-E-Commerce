@if (request()->routeIs('home'))
    <div class="modal fade" id="promoPopupModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 bg-transparent shadow-none">
                <div class="modal-body p-0 position-relative text-center">
                    <!-- Nút đóng góc trên bên phải ảnh -->
                    <button type="button"
                        class="btn-close btn-close-white position-absolute top-0 end-0 m-3 p-2 bg-dark rounded-circle opacity-100 shadow"
                        data-bs-dismiss="modal" aria-label="Close" id="btnClosePromoModal"
                        style="z-index: 1056; border: 2px solid #fff;">
                    </button>

                    <!-- Thẻ link click chuyển tới cửa hàng -->
                    <a href="{{ route('books.index') }}"
                        class="d-block text-decoration-none rounded-4 overflow-hidden shadow-lg">
                        <img src="{{ asset('images/popup.png') }}" alt="Chương Trình Khuyến Mãi BOOK & BOX"
                            class="img-fluid w-100 rounded-4">
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const promoModalEl = document.getElementById('promoPopupModal');
            if (!promoModalEl) return;

            // Kiểm tra xem người dùng đã từng tắt popup này chưa
            const isClosed = localStorage.getItem('bookbox_promo_closed');

            if (!isClosed) {
                // Tự động hiển thị Modal sau khi trang tải xong 0.5s
                setTimeout(() => {
                    if (typeof bootstrap !== 'undefined') {
                        const promoModal = new bootstrap.Modal(promoModalEl);
                        promoModal.show();
                    }
                }, 500);
            }

            // Ghi nhớ vào localStorage khi bấm nút Đóng hoặc đóng Modal
            promoModalEl.addEventListener('hidden.bs.modal', function() {
                localStorage.setItem('bookbox_promo_closed', 'true');
            });
        });
    </script>
@endif
