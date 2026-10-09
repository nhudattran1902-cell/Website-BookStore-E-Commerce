@extends('layouts.app')

@section('title', ($book->tieu_de ?? 'Chi tiết sách') . ' - BOOK & BOX')

@section('content')
    <div class="container py-5">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        <div class="row">
            <!-- Ảnh bìa sách -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <img src="{{ $book->anh_bia_url ?: 'https://via.placeholder.com/300x450' }}"
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

                    <span class="stock-status {{ $soLuongTon > 0 ? 'stock-status--available' : 'stock-status--unavailable' }}">
                        <span class="stock-status__dot" aria-hidden="true"></span>
                        <span>{{ $soLuongTon > 0 ? 'Còn hàng' : 'Hết hàng' }}</span>
                        @if ($soLuongTon > 0)
                            <span class="stock-status__quantity">{{ number_format($soLuongTon) }} cuốn khả dụng</span>
                        @endif
                    </span>

                    @if ($soLuongTon > 0)
                        <form action="{{ route('cart.add') }}" method="POST" class="d-flex gap-2"
                            data-cart-form>
                            @csrf
                            <input type="hidden" name="id_sach" value="{{ $book->id }}">
                            <input type="number" name="so_luong" value="1" min="1" max="{{ $soLuongTon }}"
                                class="form-control rounded-0" style="width: 80px;" aria-label="Số lượng">
                            <button type="submit" class="btn btn-dark px-4 py-2 rounded-0" data-cart-submit>
                                <i class="bi bi-cart-plus me-1"></i> Thêm vào giỏ hàng
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-dark px-4 py-2 rounded-0" disabled>
                            <i class="bi bi-cart-plus me-1"></i> Hết hàng
                        </button>
                    @endif

                    @auth
                        @if (Auth::user()->sachYeuThich()->whereKey($book->id)->exists())
                            <form action="{{ route('customer.wishlist.destroy', $book) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger rounded-0">
                                    <i class="bi bi-heart-fill me-1"></i> Đã yêu thích
                                </button>
                            </form>
                        @else
                            <form action="{{ route('customer.wishlist.store', $book) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger rounded-0">
                                    <i class="bi bi-heart me-1"></i> Yêu thích
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-danger rounded-0">
                            <i class="bi bi-heart me-1"></i> Đăng nhập để yêu thích
                        </a>
                    @endauth

                    @if (($book->khoHang?->so_luong_kha_dung ?? 0) < 1)
                        @auth
                            @if ($isFollowingStock)
                                <a href="{{ route('customer.stock-alerts.index') }}" class="btn btn-outline-primary rounded-0">
                                    <i class="bi bi-bell-fill me-1"></i> Đang theo dõi khi có hàng
                                </a>
                            @else
                                <form action="{{ route('customer.stock-alerts.store', $book) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary rounded-0">
                                        <i class="bi bi-bell me-1"></i> Báo tôi khi có hàng
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-0">
                                <i class="bi bi-bell me-1"></i> Đăng nhập để theo dõi
                            </a>
                        @endauth
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
                    @if ($book->loai_bia || $book->so_trang || $book->ngon_ngu || $book->lan_tai_ban || $book->khoi_luong_gram || $book->chieu_rong_mm || $book->chieu_cao_mm || $book->do_day_mm || $book->nha_cung_cap)
                        <div class="card border-0 bg-light mt-3">
                            <div class="card-body py-3">
                                <h6 class="fw-bold mb-2">Thông số sách</h6>
                                <div class="row g-2 small">
                                    @if ($book->loai_bia)
                                        <div class="col-sm-6"><strong>Bìa:</strong> {{ $book->loai_bia === 'bia_cung' ? 'Bìa cứng' : 'Bìa mềm' }}</div>
                                    @endif
                                    @if ($book->so_trang)
                                        <div class="col-sm-6"><strong>Số trang:</strong> {{ number_format($book->so_trang) }}</div>
                                    @endif
                                    @if ($book->ngon_ngu)
                                        <div class="col-sm-6"><strong>Ngôn ngữ:</strong> {{ $book->ngon_ngu }}</div>
                                    @endif
                                    @if ($book->lan_tai_ban)
                                        <div class="col-sm-6"><strong>Tái bản:</strong> Lần {{ $book->lan_tai_ban }}</div>
                                    @endif
                                    @if ($book->khoi_luong_gram)
                                        <div class="col-sm-6"><strong>Khối lượng:</strong> {{ number_format($book->khoi_luong_gram) }} g</div>
                                    @endif
                                    @if ($book->chieu_rong_mm || $book->chieu_cao_mm || $book->do_day_mm)
                                        <div class="col-sm-6">
                                            <strong>Kích thước:</strong>
                                            {{ $book->chieu_rong_mm ? number_format((float) $book->chieu_rong_mm, 1) : '—' }} ×
                                            {{ $book->chieu_cao_mm ? number_format((float) $book->chieu_cao_mm, 1) : '—' }} ×
                                            {{ $book->do_day_mm ? number_format((float) $book->do_day_mm, 1) : '—' }} mm
                                        </div>
                                    @endif
                                    @if ($book->nha_cung_cap)
                                        <div class="col-12"><strong>Nhà cung cấp:</strong> {{ $book->nha_cung_cap }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
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
            <div class="review-list" id="review-interactions" data-csrf-token="{{ csrf_token() }}">
                @forelse($reviews as $review)
                    <article class="review-card border-bottom pb-4 mb-4" data-review-card data-review-id="{{ $review->id }}">
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

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            @auth
                                @php
                                    $hasLikedReview = $review->luotThich->isNotEmpty();
                                @endphp
                                <button type="button"
                                    class="btn btn-sm {{ $hasLikedReview ? 'btn-outline-danger' : 'btn-outline-dark' }} review-like-button"
                                    data-review-like
                                    data-url="{{ route('books.reviews.likes.toggle', $review->id) }}"
                                    aria-pressed="{{ $hasLikedReview ? 'true' : 'false' }}">
                                    <i class="bi bi-hand-thumbs-up me-1" aria-hidden="true"></i>
                                    Đồng thuận (<span data-review-like-count>{{ $review->luot_thich_count }}</span>)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-dark"
                                    data-review-reply-toggle aria-expanded="false">
                                    <i class="bi bi-chat-dots me-1" aria-hidden="true"></i>
                                    Phản hồi (<span data-review-reply-count>{{ $review->binh_luans_count }}</span>)
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-hand-thumbs-up me-1" aria-hidden="true"></i>
                                    Đồng thuận ({{ $review->luot_thich_count }})
                                </a>
                                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-chat-dots me-1" aria-hidden="true"></i>
                                    Phản hồi ({{ $review->binh_luans_count }})
                                </a>
                            @endauth
                        </div>

                        <div class="review-replies mt-3" data-review-replies>
                            @foreach ($review->binhLuans as $reply)
                                <div class="review-reply border-start ps-3 py-2 mb-2">
                                    <div class="d-flex flex-wrap align-items-baseline gap-2 mb-1">
                                        <strong class="small">{{ $reply->nguoiDung->ho_ten ?? 'Khách hàng' }}</strong>
                                        <small class="text-muted">{{ $reply->ngay_tao }}</small>
                                    </div>
                                    <p class="small text-secondary mb-0" style="white-space: pre-line">{{ $reply->noi_dung }}</p>
                                </div>
                            @endforeach
                        </div>

                        @auth
                            <form class="review-reply-form d-none mt-3" data-review-reply-form
                                action="{{ route('books.reviews.replies.store', $review->id) }}" method="POST">
                                @csrf
                                <label class="form-label small fw-semibold" for="review-reply-{{ $review->id }}">Viết phản hồi</label>
                                <textarea id="review-reply-{{ $review->id }}" name="noi_dung" class="form-control form-control-sm"
                                    rows="2" maxlength="1000" required placeholder="Chia sẻ phản hồi của bạn..."></textarea>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-danger" data-review-reply-error role="alert"></small>
                                    <button type="submit" class="btn btn-sm btn-dark">
                                        <i class="bi bi-send me-1" aria-hidden="true"></i>Gửi phản hồi
                                    </button>
                                </div>
                            </form>
                        @endauth
                    </article>
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
                                        <img src="{{ $trang->duong_dan_anh_url }}"
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

@pushOnce('scripts', 'review-interactions')
    <script>
        const reviewInteractionRoot = document.getElementById('review-interactions');
        const reviewCsrfToken = reviewInteractionRoot?.dataset.csrfToken;

        document.addEventListener('click', async (event) => {
            const replyToggle = event.target.closest('[data-review-reply-toggle]');
            if (replyToggle) {
                const card = replyToggle.closest('[data-review-card]');
                const form = card.querySelector('[data-review-reply-form]');
                const isExpanded = replyToggle.getAttribute('aria-expanded') === 'true';

                replyToggle.setAttribute('aria-expanded', String(!isExpanded));
                form.classList.toggle('d-none', isExpanded);
                if (!isExpanded) {
                    form.querySelector('textarea').focus();
                }

                return;
            }

            const likeButton = event.target.closest('[data-review-like]');
            if (!likeButton || likeButton.disabled) {
                return;
            }

            likeButton.disabled = true;

            try {
                const response = await fetch(likeButton.dataset.url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': reviewCsrfToken,
                    },
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Không thể cập nhật lượt đồng thuận.');
                }

                likeButton.querySelector('[data-review-like-count]').textContent = data.count;
                likeButton.setAttribute('aria-pressed', String(data.liked));
                likeButton.classList.toggle('btn-outline-danger', data.liked);
                likeButton.classList.toggle('btn-outline-dark', !data.liked);
                likeButton.removeAttribute('title');
            } catch (error) {
                likeButton.title = error.message;
            } finally {
                likeButton.disabled = false;
            }
        });

        document.addEventListener('submit', async (event) => {
            const form = event.target.closest('[data-review-reply-form]');
            if (!form) {
                return;
            }

            event.preventDefault();
            const textarea = form.querySelector('textarea[name="noi_dung"]');
            const submitButton = form.querySelector('button[type="submit"]');
            const errorMessage = form.querySelector('[data-review-reply-error]');
            const card = form.closest('[data-review-card]');
            const content = textarea.value.trim();

            if (!content || submitButton.disabled) {
                return;
            }

            submitButton.disabled = true;
            errorMessage.textContent = '';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': reviewCsrfToken,
                    },
                    body: new FormData(form),
                });
                const data = await response.json();

                if (!response.ok) {
                    const validationMessage = Object.values(data.errors || {}).flat()[0];
                    throw new Error(validationMessage || data.message || 'Không thể gửi phản hồi.');
                }

                const reply = document.createElement('div');
                reply.className = 'review-reply border-start ps-3 py-2 mb-2';

                const heading = document.createElement('div');
                heading.className = 'd-flex flex-wrap align-items-baseline gap-2 mb-1';
                const author = document.createElement('strong');
                author.className = 'small';
                author.textContent = data.reply.user_name;
                const timestamp = document.createElement('small');
                timestamp.className = 'text-muted';
                timestamp.textContent = data.reply.ngay_tao;
                heading.append(author, timestamp);

                const replyContent = document.createElement('p');
                replyContent.className = 'small text-secondary mb-0';
                replyContent.style.whiteSpace = 'pre-line';
                replyContent.textContent = data.reply.noi_dung;
                reply.append(heading, replyContent);
                card.querySelector('[data-review-replies]').append(reply);

                const count = card.querySelector('[data-review-reply-count]');
                count.textContent = Number(count.textContent) + 1;
                textarea.value = '';
            } catch (error) {
                errorMessage.textContent = error.message;
            } finally {
                submitButton.disabled = false;
            }
        });
    </script>
@endPushOnce
