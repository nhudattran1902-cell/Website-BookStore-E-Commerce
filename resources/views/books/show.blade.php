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
                    <!-- Form gửi tới CartController@addToCart -->
                    <form action="{{ route('cart.add') }}" method="POST" class="d-flex gap-2">
                        @csrf
                        <input type="hidden" name="id_sach" value="{{ $book->id }}">
                        <input type="number" name="so_luong" value="1" min="1" class="form-control rounded-0"
                            style="width: 80px;">
                        <button type="submit" class="btn btn-dark px-4 py-2 rounded-0">
                            <i class="bi bi-cart-plus me-1"></i> Thêm vào giỏ hàng
                        </button>
                    </form>

                    <!-- Nút Đọc ngay (Bật Modal xem trang đọc thử) -->
                    @if (isset($previewPages) && $previewPages->count() > 0)
                        <button class="btn btn-warning px-4 py-2 rounded-0 text-white fw-bold" data-bs-toggle="modal"
                            data-bs-target="#modalDocThu">
                            <i class="bi bi-book me-1"></i> Đọc ngay
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
    </div>

    <!-- MODAL POPUP XEM TRANG ĐỌC THỬ (Dựa trên $previewPages từ BookController) -->
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
