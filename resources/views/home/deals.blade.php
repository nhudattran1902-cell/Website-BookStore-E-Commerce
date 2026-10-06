<section class="bg-theme-light py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h3 class="fw-bold mb-0">Ưu đãi tuần này</h3>
            <a href="{{ route('books.index', ['sort' => 'price_asc']) }}" class="text-dark text-decoration-none fw-medium small">Xem tất cả  <i
                    class="fas fa-chevron-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            @forelse ($weeklyDeals as $deal)
                <div class="col-md-6">
                    <article class="card p-3 h-100">
                        <div class="row g-0 align-items-center h-100">
                            <div class="col-4">
                                <a href="{{ route('books.show', $deal->id) }}" class="img-wrapper ratio-deal d-block">
                                    <img src="{{ $deal->anh_bia_url ?: asset('images/no-cover.jpg') }}"
                                        class="w-100 h-100 object-fit-cover" alt="{{ $deal->tieu_de }}" loading="lazy">
                                </a>
                            </div>
                            <div class="col-8 px-4">
                                <small class="text-theme text-uppercase fw-bold d-block mb-2">Ưu đãi BOOK & BOX</small>
                                <h5 class="fw-bold">{{ $deal->tieu_de }}</h5>
                                <p class="text-muted small mb-2">{{ $deal->tacGia->pluck('ten_tac_gia')->implode(', ') ?: 'Đang cập nhật tác giả' }}</p>
                                <h4 class="fw-bold text-danger mb-1">
                                    {{ number_format($deal->gia_khuyen_mai, 0, ',', '.') }} đ
                                    <small class="text-muted text-decoration-line-through fs-6">{{ number_format($deal->gia_ban, 0, ',', '.') }} đ</small>
                                </h4>
                                <small class="text-muted">Tiết kiệm {{ number_format(100 - ($deal->gia_khuyen_mai / $deal->gia_ban * 100)) }}%</small>
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-muted mb-0">Hiện chưa có ưu đãi. Các chương trình mới sẽ được cập nhật tại đây.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
