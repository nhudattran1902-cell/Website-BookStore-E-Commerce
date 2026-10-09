@extends('layouts.app')

@section('title', 'Sách yêu thích - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 fw-bold mb-1">Sách yêu thích</h1>
                <p class="text-muted mb-0">Những cuốn sách bạn đã lưu.</p>
            </div>
            <a href="{{ route('books.index') }}" class="btn btn-outline-dark">Tiếp tục xem sách</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($books->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-heart fs-1 text-muted"></i>
                    <p class="mt-3 mb-0">Danh sách yêu thích của bạn đang trống.</p>
                </div>
            </div>
        @else
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
                @foreach ($books as $book)
                    <div class="col">
                        <article class="card h-100 border-0 shadow-sm">
                            <a href="{{ route('books.show', $book->id) }}">
                                <img src="{{ $book->anh_bia_url ?: asset('images/no-cover.jpg') }}"
                                    class="card-img-top" alt="{{ $book->tieu_de }}" style="height: 260px; object-fit: cover;">
                            </a>
                            <div class="card-body d-flex flex-column">
                                <h2 class="h6 fw-bold">
                                    <a href="{{ route('books.show', $book->id) }}" class="text-dark text-decoration-none">
                                        {{ $book->tieu_de }}
                                    </a>
                                </h2>
                                <p class="text-muted small">{{ $book->tacGia->pluck('ten_tac_gia')->implode(', ') ?: 'Đang cập nhật' }}</p>
                                <p class="fw-bold text-danger mt-auto">{{ number_format($book->gia_khuyen_mai ?? $book->gia_ban, 0, ',', '.') }} đ</p>
                                <form action="{{ route('customer.wishlist.destroy', $book) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger w-100" type="submit">
                                        <i class="bi bi-heartbreak me-1"></i> Bỏ yêu thích
                                    </button>
                                </form>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $books->links() }}</div>
        @endif
    </div>
@endsection
