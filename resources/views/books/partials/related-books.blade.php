{{-- resources/views/books/partials/related-books.blade.php --}}
@if(isset($books) && $books->count() > 0)
    <div class="related-books-section mt-5 pt-4 border-top">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark mb-0">
                <i class="bi bi-collection-fill text-danger me-2"></i>Sách Cùng Thể Loại
            </h4>
            @if(isset($category))
                <a href="{{ route('books.index', ['the_loai' => $category->id]) }}" class="btn btn-sm btn-outline-danger rounded-pill">
                    Xem tất cả <i class="bi bi-arrow-right"></i>
                </a>
            @endif
        </div>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
            @foreach($books as $relBook)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative hover-top">
                        <!-- Ảnh bìa -->
                        <a href="{{ route('books.show', $relBook->id) }}" class="text-decoration-none">
                            <div class="ratio ratio-3x4 bg-light">
                                <img src="{{ $relBook->anh_bia ? asset('storage/' . $relBook->anh_bia) : asset('images/no-cover.jpg') }}" 
                                     class="card-img-top object-fit-cover" 
                                     alt="{{ $relBook->tieu_de }}"
                                     loading="lazy">
                            </div>
                        </a>

                        <div class="card-body d-flex flex-column p-3">
                            <!-- Thể loại -->
                            <small class="text-muted mb-1 text-truncate">
                                {{ $relBook->theLoai->ten_the_loai ?? 'Sách' }}
                            </small>

                            <!-- Tiêu đề -->
                            <h6 class="card-title fw-bold mb-2">
                                <a href="{{ route('books.show', $relBook->id) }}" class="text-dark text-decoration-none text-truncate-2">
                                    {{ $relBook->tieu_de }}
                                </a>
                            </h6>

                            <!-- Tác giả -->
                            <p class="card-text small text-secondary mb-3 text-truncate">
                                {{ $relBook->tacGia->pluck('ten_tac_gia')->implode(', ') ?: 'Nhiều tác giả' }}
                            </p>

                            <!-- Giá & Nút xem chi tiết -->
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-danger fs-6">
                                        {{ number_format($relBook->gia_khuyen_mai ?? $relBook->gia_ban, 0, ',', '.') }} đ
                                    </span>
                                    @if($relBook->gia_khuyen_mai)
                                        <small class="text-muted text-decoration-line-through d-block" style="font-size: 0.75rem;">
                                            {{ number_format($relBook->gia_ban, 0, ',', '.') }} đ
                                        </small>
                                    @endif
                                </div>
                                <a href="{{ route('books.show', $relBook->id) }}" class="btn btn-sm btn-light border rounded-circle" title="Xem chi tiết">
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