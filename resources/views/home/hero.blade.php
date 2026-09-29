<div id="heroCarousel" class="carousel slide carousel-fade mb-5 shadow-sm rounded-4 overflow-hidden"
    data-bs-ride="carousel" data-bs-interval="5000">
    <!-- Carousel Indicators -->
    <div class="carousel-indicators mb-3">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true"
            aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>

    <!-- Carousel Inner -->
    <div class="carousel-inner">
        <!-- Slide 1 -->
        <div class="carousel-item active">
            <div class="position-relative bg-dark text-white rounded-4 overflow-hidden" style="min-height: 380px;">
                <img src="{{ asset('images/banner1.jpg') }}"
                    class="d-block w-100 object-fit-cover position-absolute top-0 start-0 h-100 opacity-75"
                    alt="Khuyến mãi sách mới"
                    onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">

                <!-- Fallback background when image is missing or errors -->
                <div class="d-none position-absolute top-0 start-0 w-100 h-100 bg-gradient"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);"></div>

                <div class="position-relative z-1 p-4 p-md-5 d-flex flex-column justify-content-center h-100"
                    style="min-height: 380px;">
                    <span class="badge bg-danger rounded-pill align-self-start mb-3 px-3 py-2 fs-6">
                        <i class="bi bi-fire me-1"></i> Siêu Ưu Đãi Tháng 10
                    </span>
                    <h1 class="display-5 fw-bold mb-3 text-white">Khám Phá Thế Giới Sách Mới</h1>
                    <p class="fs-5 mb-4 text-light text-truncate-2" style="max-width: 600px;">
                        Giảm giá lên đến 50% cho hàng ngàn đầu sách hot nhất, miễn phí vận chuyển cho đơn hàng từ
                        250.000đ.
                    </p>
                    <div>
                        <a href="{{ route('books.index') }}"
                            class="btn btn-warning btn-lg fw-bold px-4 rounded-pill shadow-sm">
                            <i class="bi bi-cart-plus me-2"></i> Mua Ngay
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="carousel-item">
            <div class="position-relative bg-dark text-white rounded-4 overflow-hidden" style="min-height: 380px;">
                <img src="{{ asset('images/banner2.jpg') }}"
                    class="d-block w-100 object-fit-cover position-absolute top-0 start-0 h-100 opacity-75"
                    alt="Manga Comic Hot"
                    onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">

                <!-- Fallback background -->
                <div class="d-none position-absolute top-0 start-0 w-100 h-100 bg-gradient"
                    style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);"></div>

                <div class="position-relative z-1 p-4 p-md-5 d-flex flex-column justify-content-center h-100"
                    style="min-height: 380px;">
                    <span class="badge bg-warning text-dark rounded-pill align-self-start mb-3 px-3 py-2 fs-6">
                        <i class="bi bi-star-fill me-1"></i> Đặc Biệt Hot
                    </span>
                    <h2 class="display-5 fw-bold mb-3 text-white">Thế Giới Manga & Comic</h2>
                    <p class="fs-5 mb-4 text-light text-truncate-2" style="max-width: 600px;">
                        Cập nhật liên tục các tập mới nhất của Doraemon, Thanh Gươm Diệt Quỷ và các bộ truyện hấp dẫn.
                    </p>
                    <div>
                        <a href="{{ route('books.index') }}"
                            class="btn btn-light btn-lg fw-bold px-4 rounded-pill shadow-sm text-dark">
                            <i class="bi bi-book me-2"></i> Xem Cửa Hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="carousel-item">
            <div class="position-relative bg-dark text-white rounded-4 overflow-hidden" style="min-height: 380px;">
                <img src="{{ asset('images/banner3.jpg') }}"
                    class="d-block w-100 object-fit-cover position-absolute top-0 start-0 h-100 opacity-75"
                    alt="Sách kỹ năng sống"
                    onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">

                <!-- Fallback background -->
                <div class="d-none position-absolute top-0 start-0 w-100 h-100 bg-gradient"
                    style="background: linear-gradient(135deg, #8E2DE2 0%, #4A00E0 100%);"></div>

                <div class="position-relative z-1 p-4 p-md-5 d-flex flex-column justify-content-center h-100"
                    style="min-height: 380px;">
                    <span class="badge bg-info rounded-pill align-self-start mb-3 px-3 py-2 fs-6">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Phát Triển Bản Thân
                    </span>
                    <h2 class="display-5 fw-bold mb-3 text-white">Sách Kỹ Năng & Kinh Tế</h2>
                    <p class="fs-5 mb-4 text-light text-truncate-2" style="max-width: 600px;">
                        Nâng tầm tri thức với bộ sưu tập các cuốn sách bán chạy nhất về tư duy, khởi nghiệp và kinh
                        doanh.
                    </p>
                    <div>
                        <a href="{{ route('books.index') }}"
                            class="btn btn-warning btn-lg fw-bold px-4 rounded-pill shadow-sm">
                            <i class="bi bi-compass me-2"></i> Khám Phá
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Carousel Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Trước</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Sau</span>
    </button>
</div>
