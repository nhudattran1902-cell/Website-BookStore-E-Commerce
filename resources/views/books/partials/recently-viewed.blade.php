{{-- resources/views/books/partials/recently-viewed.blade.php --}}
@if(isset($recentlyViewedBooks) && $recentlyViewedBooks->count() > 0)
    <div class="recently-viewed-section mt-5 pt-4 border-top">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark mb-0">
                <i class="bi bi-clock-history text-primary me-2"></i>Sách Bạn Đã Xem Gần Đây
            </h4>
        </div>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
            @foreach($recentlyViewedBooks as $viewedBook)
                <div class="col">
                    <div class="card book-card discovery-book-card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-top">
                        <a href="{{ route('books.show', $viewedBook->id) }}" class="text-decoration-none">
                            <div class="ratio ratio-3x4 bg-light">
                                <img src="{{ $viewedBook->anh_bia_url ?: asset('images/logo.jpg') }}"
                                     class="card-img-top object-fit-cover discovery-book-cover"
                                     alt="{{ $viewedBook->tieu_de }}"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src='{{ asset('images/logo.jpg') }}';">
                            </div>
                        </a>

                        <div class="card-body d-flex flex-column p-3">
                            <small class="text-muted mb-1 text-truncate">
                                {{ $viewedBook->theLoai->ten_the_loai ?? 'Sách' }}
                            </small>

                            <h6 class="card-title fw-bold mb-2">
                                <a href="{{ route('books.show', $viewedBook->id) }}" class="text-dark text-decoration-none text-truncate-2">
                                    {{ $viewedBook->tieu_de }}
                                </a>
                            </h6>

                            <p class="card-text small text-secondary mb-3 text-truncate">
                                {{ $viewedBook->tacGia->pluck('ten_tac_gia')->implode(', ') ?: 'Nhiều tác giả' }}
                            </p>

                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-danger fs-6">
                                        {{ number_format($viewedBook->gia_khuyen_mai ?? $viewedBook->gia_ban, 0, ',', '.') }} đ
                                    </span>
                                </div>
                                <a href="{{ route('books.show', $viewedBook->id) }}" class="btn btn-sm btn-light border rounded-circle" title="Xem chi tiết">
                                    <i class="bi bi-eye text-dark"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif