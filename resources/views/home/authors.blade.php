<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Tác Giả Nổi Bật</h3>
                <p class="text-muted small mb-0">Những tác giả được yêu thích nhất tại BOOK & BOX</p>
            </div>
            <a href="{{ route('books.index') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                Xem tất cả <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-4">
            @forelse($authors as $author)
                <div class="col text-center">
                    <div class="card h-100 border-0 shadow-sm rounded-4 p-3 hover-top transition">
                        <div class="position-relative mx-auto mb-3" style="width: 100px; height: 100px;">
                            <img src="{{ $author->anh_dai_dien ? asset('storage/' . $author->anh_dai_dien) : 'https://ui-avatars.com/api/?name=' . urlencode($author->ten_tac_gia) . '&background=f8d7da&color=dc3545&size=100' }}"
                                class="rounded-circle w-100 h-100 object-fit-cover border border-2 border-danger-subtle p-1"
                                alt="{{ $author->ten_tac_gia }}">
                        </div>
                        <h6 class="fw-bold mb-1 text-truncate" title="{{ $author->ten_tac_gia }}">
                            {{ $author->ten_tac_gia }}
                        </h6>
                        <small class="text-muted">
                            <i class="bi bi-book me-1 text-danger"></i>{{ $author->sach_count }} Tác phẩm
                        </small>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-4">
                    <p class="text-muted mb-0">Chưa có thông tin tác giả.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
