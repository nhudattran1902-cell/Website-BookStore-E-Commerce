<section class="bg-theme-light py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h3 class="fw-bold mb-0">Giảm giá hàng tuần</h3>
            <a href="{{ route('books.index', ['sort' => 'price_asc']) }}" class="text-dark text-decoration-none fw-medium small">Xem tất cả  <i
                    class="fas fa-chevron-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            <!-- Deal Card 1 -->
            <div class="col-md-6">
                <div class="card p-3 border-0 rounded-0 h-100">
                    <div class="row g-0 align-items-center h-100">
                        <div class="col-4">
                            <div class="img-wrapper ratio-deal">
                                <div class="loading-spinner"></div>
                                <img src="https://via.placeholder.com/150x220/ecf0f1/000000?text=Tasteful"
                                    class="lazy-img" alt="Deal 1" />
                            </div>
                        </div>
                        <div class="col-8 px-4">
                            <small class="text-theme text-uppercase fw-bold d-block mb-2"
                                style="font-size: 0.7rem">Kindle Edition</small>
                            <h5 class="fw-bold">Dark in Death: An Eve Dallas Novel</h5>
                            <p class="text-muted small mb-2">Nora Roberts</p>
                            <h4 class="fw-bold text-danger mb-3">
                                $79
                                <small class="text-muted text-decoration-line-through fs-6">$99</small>
                            </h4>
                            <div class="progress mt-4" style="height: 6px">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 80%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Deal Card 2 -->
            <div class="col-md-6">
                <div class="card p-3 border-0 rounded-0 h-100">
                    <div class="row g-0 align-items-center h-100">
                        <div class="col-4">
                            <div class="img-wrapper ratio-deal">
                                <div class="loading-spinner"></div>
                                <img src="https://via.placeholder.com/150x220/3498db/ffffff?text=Kindness"
                                    class="lazy-img" alt="Deal 2" />
                            </div>
                        </div>
                        <div class="col-8 px-4">
                            <small class="text-theme text-uppercase fw-bold d-block mb-2"
                                style="font-size: 0.7rem">Kindle Edition</small>
                            <h5 class="fw-bold">Under a Firefly Moon</h5>
                            <p class="text-muted small mb-2">Nora Roberts</p>
                            <h4 class="fw-bold text-danger mb-3">
                                $79
                                <small class="text-muted text-decoration-line-through fs-6">$99</small>
                            </h4>
                            <div class="progress mt-4" style="height: 6px">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 80%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
