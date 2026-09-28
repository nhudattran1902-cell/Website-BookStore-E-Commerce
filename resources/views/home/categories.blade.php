<section class="container py-5 mt-4">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <h3 class="fw-bold mb-0">Danh mục nổi bật</h3>
        <a href="{{ route('books.index') }}" class="text-dark text-decoration-none fw-medium small">Tất cả danh mục <i
                class="fas fa-chevron-right ms-1"></i></a>
    </div>
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-6 g-4">
        @foreach ($categories as $category)
            <div class="col">
                <div class="card p-4 h-100 border-0 shadow-sm text-center rounded-3 bg-light">
                    <div class="mb-3 text-dark">
                        <i class="fas fa-book-open fs-2"></i>
                    </div>
                    <h6 class="fw-bold mb-2">{{ $category->ten_the_loai }}</h6>
                    <a href="{{ route('books.index', ['the_loai' => $category->id]) }}"
                        class="text-dark text-decoration-none small mt-auto">Xem ngay</a>
                </div>
            </div>
        @endforeach
    </div>
</section>
