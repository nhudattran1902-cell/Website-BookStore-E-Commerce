<!-- 5. Bestselling Books -->
<section class="container py-5 border-top">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <h3 class="fw-bold mb-0">Sách bán chạy nhất</h3>
        <a href="{{ route('books.index', ['sort' => 'price_desc']) }}" class="text-dark text-decoration-none fw-medium small">Xem tất cả <i
                class="fas fa-chevron-right ms-1"></i></a>
    </div>

    <div class="row row-cols-2 row-cols-md-5 g-4">
        <!-- Vòng lặp qua danh sách sách bán chạy -->
        @foreach ($bestsellingBooks as $book)
            <div class="col">
                <div class="card book-card h-100">
                    <div class="img-wrapper ratio-book card-img-top rounded-0">
                        <a href="{{ route('books.show', $book->id) }}" class="d-block w-100 h-100">
                            <div class="loading-spinner"></div>
                            <img src="{{ $book->anh_bia ? asset('storage/' . $book->anh_bia) : 'https://via.placeholder.com/200x300/bdc3c7/ffffff?text=Cover' }}"
                                class="lazy-img" alt="{{ $book->tieu_de }}" />
                        </a>
                    </div>
                    <div class="card-body px-0">
                        <small class="text-theme text-uppercase fw-bold d-block mb-1"
                            style="font-size: 0.7rem">Paperback</small>

                        <!-- Tên sách từ cột tieu_de -->
                        <h6 class="card-title fw-bold text-truncate">
                            <a href="{{ route('books.show', $book->id) }}" class="text-dark text-decoration-none">{{ $book->tieu_de }}</a>
                        </h6>

                        <!-- Tác giả từ bảng liên kết tac_gia -->
                        <p class="text-muted small mb-2">{{ $book->author_name ?? 'Đang cập nhật' }}</p>

                        <!-- Giá bán từ cột gia_ban -->
                        <h5 class="fw-bold mb-0">${{ number_format($book->gia_ban, 2) }}</h5>

                        @if ((int) $book->so_luong_ton > 0)
                            <form action="{{ route('cart.add') }}" method="POST" class="mt-2">
                                @csrf
                                <input type="hidden" name="id_sach" value="{{ $book->id }}">
                                <input type="hidden" name="so_luong" value="1">
                                <button type="submit" class="btn btn-sm btn-dark w-100 rounded-0">Thêm vào giỏ</button>
                            </form>
                        @else
                            <button type="button" class="btn btn-sm btn-danger w-100 rounded-0 mt-2" disabled>
                                HẾT HÀNG
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
