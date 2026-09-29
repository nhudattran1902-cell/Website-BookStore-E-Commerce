@extends('layouts.app')

@section('title', ($book->tieu_de ?? 'Chi tiết sách') . ' - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row">
            <!-- Ảnh bìa sách -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <img src="{{ $book->anh_bia ? asset('storage/' . $book->anh_bia) : 'https://via.placeholder.com/300x450' }}"
                        class="img-fluid rounded-0 w-100" alt="{{ $book->tieu_de }}">
                </div>
            </div>

            <!-- Thông tin chi tiết sách -->
            <div class="col-md-8">
                <h1 class="fw-bold mb-3">{{ $book->tieu_de }}</h1>

                <p class="text-muted mb-2">
                    <strong>Giá bán:</strong>
                    <span class="text-danger fs-4 fw-bold">
                        {{ number_format($book->gia_khuyen_mai ?? ($book->gia_ban ?? 0), 0, ',', '.') }} đ
                    </span>
                    @if (!empty($book->gia_khuyen_mai) && $book->gia_khuyen_mai < $book->gia_ban)
                        <small class="text-muted text-decoration-line-through ms-2">
                            {{ number_format($book->gia_ban, 0, ',', '.') }} đ
                        </small>
                    @endif
                </p>

                <p class="text-secondary mb-4">
                    <strong>Mô tả:</strong> {{ $book->mo_ta ?? 'Chưa có mô tả cho cuốn sách này.' }}
                </p>

                <!-- Thao tác: Thêm vào giỏ & Đọc thử -->
                <div class="d-flex flex-wrap gap-3 mb-4 align-items-center">
                    @php
                        $soLuongTon = $book->khoHang->so_luong_ton ?? 0;
                    @endphp

                    @if ($soLuongTon > 0)
                        {{-- Form thêm vào giỏ hàng bình thường --}}
                        <form action="{{ route('cart.add') }}" method="POST" class="d-flex gap-2">
                            @csrf
                            <input type="hidden" name="id_sach" value="{{ $book->id }}">
                            <input type="number" name="so_luong" value="1" min="1" max="{{ $soLuongTon }}"
                                class="form-control rounded-0" style="width: 80px;">
                            <button type="submit" class="btn btn-dark px-4 py-2 rounded-0">
                                <i class="bi bi-cart-plus me-1"></i> Thêm vào giỏ hàng
                            </button>
                        </form>
                    @else
                        {{-- Khóa nút mua và hiển thị badge HẾT HÀNG --}}
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-danger px-4 py-2 rounded-0 disabled" disabled>
                                <i class="bi bi-x-circle me-1"></i> HẾT HÀNG
                            </button>
                            <span class="badge bg-danger fs-6 py-2 px-3">Tạm hết hàng trong kho</span>
                        </div>
                    @endif

                    <!-- Nút Đọc ngay (Luôn hiển thị) -->
                    @if (isset($previewPages) && $previewPages->count() > 0)
                        {{-- Nếu đã có trang đọc thử: Bật Modal --}}
                        <button class="btn btn-warning px-4 py-2 rounded-0 text-white fw-bold" data-bs-toggle="modal"
                            data-bs-target="#modalDocThu">
                            <i class="bi bi-book me-1"></i> Đọc ngay
                        </button>
                    @else
                        {{-- Nếu chưa có trang đọc thử: Hiển thị nút bị vô hiệu hóa kèm Tooltip / Thông báo --}}
                        <button class="btn btn-secondary px-4 py-2 rounded-0 text-white fw-bold opacity-75" disabled
                            title="Cuốn sách này hiện chưa hỗ trợ đọc thử">
                            <i class="bi bi-book me-1"></i> Đọc ngay (Chưa có bản thử)
                        </button>
                    @endif

                    <a href="{{ route('books.index') }}" class="btn btn-outline-dark px-4 py-2 rounded-0">
                        Quay lại cửa hàng
                    </a>
                </div>

                <!-- Thông tin thêm -->
                <div class="mt-2">
                    @if (!empty($book->ten_the_loai))
                        <div class="mb-2">
                            <span class="badge bg-secondary">{{ $book->ten_the_loai }}</span>
                        </div>
                    @endif
                    @if (!empty($book->ten_nxb))
                        <p class="text-muted small mb-1"><strong>NXB:</strong> {{ $book->ten_nxb }}</p>
                    @endif
                    @if (!empty($authors) && $authors->count() > 0)
                        <p class="text-muted small mb-1"><strong>Tác giả:</strong>
                            {{ $authors->pluck('ten_tac_gia')->implode(', ') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- 1. KHỐI ĐÁNH GIÁ SẢN PHẨM -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mt-5" id="reviews-section">
            <h4 class="fw-bold mb-4"><i class="bi bi-star-fill text-warning me-2"></i>Đánh Giá Từ Khách Hàng</h4>

            <!-- Thống kê điểm trung bình -->
            <div class="row align-items-center mb-4 pb-3 border-bottom">
                <div class="col-md-4 text-center border-end">
                    <h1 class="display-4 fw-bold text-danger mb-0">{{ $avgRating > 0 ? $avgRating : '0.0' }}</h1>
                    <div class="text-warning mb-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}"></i>
                        @endfor
                    </div>
                    <small class="text-muted">Dựa trên {{ $totalReviews }} lượt đánh giá</small>
                </div>

                <!-- Form gửi đánh giá -->
                <div class="col-md-8 ps-md-4 mt-3 mt-md-0">
                    @auth
                        <form action="{{ route('books.reviews.store', $book->id) }}" method="POST">
                            @csrf
                            <h6 class="fw-bold mb-2">Viết đánh giá của bạn</h6>
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show p-2 small" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close p-2" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Chọn số sao:</label>
                                <select name="so_sao" class="form-select form-select-sm w-auto" required>
                                    <option value="5">5 Sao - Rất hài lòng</option>
                                    <option value="4">4 Sao - Hài lòng</option>
                                    <option value="3">3 Sao - Bình thường</option>
                                    <option value="2">2 Sao - Không hài lòng</option>
                                    <option value="1">1 Sao - Rất tệ</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <textarea name="binh_luan" class="form-control" rows="3"
                                    placeholder="Chia sẻ cảm nhận của bạn về cuốn sách này..." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4 fw-bold">
                                <i class="bi bi-send me-1"></i> Gửi đánh giá
                            </button>
                        </form>
                    @else
                        <div class="bg-light p-3 rounded-3 text-center">
                            <p class="mb-2 text-muted">Vui lòng đăng nhập để gửi đánh giá cho cuốn sách này.</p>
                            <a href="{{ route('login') }}" class="btn btn-outline-danger btn-sm rounded-pill px-4">Đăng
                                nhập ngay</a>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Danh sách đánh giá -->
            <div class="review-list">
                @forelse($reviews as $review)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark">{{ $review->nguoiDung->ho_ten ?? 'Khách hàng' }}</span>
                            <small class="text-muted">{{ $review->ngay_tao }}</small>
                        </div>
                        <div class="text-warning small mb-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $review->so_sao ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                        <p class="mb-0 text-secondary small">{{ $review->binh_luan }}</p>
                    </div>
                @empty
                    <p class="text-center text-muted py-3">Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá cuốn sách
                        này!</p>
                @endforelse

                <div class="d-flex justify-content-end pt-2">
                    {{ $reviews->fragment('reviews-section')->links() }}
                </div>
            </div>
        </div>

        <!-- 2. KHỐI SÁCH LIÊN QUAN CÙNG THỂ LOẠI -->
        @include('books.partials.related-books', [
            'books' => $relatedBooks,
            'category' => $book->theLoai,
        ])

        <!-- 3. KHỐI SÁCH ĐÃ XEM GẦN ĐÂY -->
        @include('books.partials.recently-viewed', [
            'recentlyViewedBooks' => $recentlyViewedBooks,
        ])
    </div>

    <!-- MODAL POPUP XEM TRANG ĐỌC THỬ -->
    @if (isset($previewPages) && $previewPages->count() > 0)
        <div class="modal fade" id="modalDocThu" tabindex="-1" aria-labelledby="modalDocThuLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalDocThuLabel">Đọc thử: {{ $book->tieu_de }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center bg-light">
                        <!-- Carousel hiển thị các trang đọc thử -->
                        <div id="carouselDocThu" class="carousel slide carousel-dark" data-bs-ride="false">
                            <div class="carousel-inner">
                                @foreach ($previewPages as $index => $trang)
                                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/' . $trang->duong_dan_anh) }}"
                                            class="d-block mx-auto img-fluid shadow-sm" style="max-height: 600px;"
                                            alt="Trang {{ $trang->so_trang }}">
                                        <div class="mt-2 text-muted fw-bold">Trang {{ $trang->so_trang }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselDocThu"
                                data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Trang trước</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselDocThu"
                                data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Trang sau</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
