@extends('layouts.app')

@section('title', 'Kết quả tìm kiếm cho: ' . $keyword . ' - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <h3 class="fw-bold mb-4">Kết quả tìm kiếm cho: <span class="text-danger">"{{ $keyword }}"</span></h3>

        @if ($books->count() > 0)
            <p class="text-muted mb-4">Tìm thấy <strong>{{ $books->total() }}</strong> kết quả phù hợp.</p>

            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
                @foreach ($books as $book)
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm product-card">
                            <a href="{{ route('books.show', $book->id) }}">
                                <img src="{{ $book->anh_bia_url ?: asset('images/no-cover.jpg') }}"
                                    class="card-img-top rounded-0" alt="{{ $book->tieu_de }}"
                                    style="height: 300px; object-fit: cover;">
                            </a>
                            <div class="card-body text-center">
                                <small
                                    class="text-muted d-block mb-1">{{ $book->theLoai->ten_the_loai ?? 'Chưa phân loại' }}</small>
                                <h6 class="card-title fw-bold mb-2">
                                    <a href="{{ route('books.show', $book->id) }}"
                                        class="text-decoration-none text-dark">{{ $book->tieu_de }}</a>
                                </h6>
                                <div class="fw-bold text-danger">{{ number_format($book->gia_ban, 0, ',', '.') }} đ</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-center">
                {{ $books->appends(['q' => $keyword])->links() }}
            </div>
        @else
            <!-- Thông báo khi không tìm thấy sách -->
            <div class="text-center py-5">
                <i class="bi bi-search display-1 text-muted mb-3 d-block"></i>
                <h4 class="fw-bold">Không tìm thấy cuốn sách nào!</h4>
                <p class="text-muted">Rất tiếc, chúng tôi không tìm thấy kết quả nào phù hợp với từ khóa
                    "{{ $keyword }}".<br>Vui lòng thử lại bằng từ khóa khác.</p>
                <a href="{{ route('books.index') }}" class="btn btn-dark mt-3 px-4">Xem tất cả sách</a>
            </div>
        @endif
    </div>
@endsection
