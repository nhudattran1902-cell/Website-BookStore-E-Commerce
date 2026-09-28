@extends('layouts.app')

@section('title', 'Cửa hàng sách - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row">
            <!-- Sidebar Lọc Thể Loại -->
            <div class="col-lg-3 mb-4">
                <h5 class="fw-bold mb-3">Thể loại sách</h5>
                <div class="list-group rounded-0 shadow-sm">
                    <a href="{{ route('books.index') }}"
                        class="list-group-item list-group-item-action {{ !request('the_loai') ? 'active bg-dark border-dark' : '' }}">
                        Tất cả sách
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ route('books.index', ['the_loai' => $cat->id]) }}"
                            class="list-group-item list-group-item-action {{ request('the_loai') == $cat->id ? 'active bg-dark border-dark' : '' }}">
                            {{ $cat->ten_the_loai }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Lưới Danh Sách Sản Phẩm -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold mb-0">Danh sách sản phẩm</h3>
                    <form action="{{ route('books.index') }}" method="GET" class="d-flex align-items-center gap-2">
                        @if (request('the_loai'))
                            <input type="hidden" name="the_loai" value="{{ request('the_loai') }}">
                        @endif
                        <label for="sort" class="text-muted small mb-0">Sắp xếp:</label>
                        <select name="sort" id="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Mới nhất</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
                        </select>
                    </form>
                </div>

                @if ($books->isEmpty())
                    <div class="alert alert-warning rounded-0">Chưa có cuốn sách nào trong danh mục này.</div>
                @else
                    <div class="row row-cols-1 row-cols-md-3 g-4">
                        @foreach ($books as $book)
                            <div class="col">
                                <div class="card book-card h-100 shadow-sm border-0">
                                    <div class="img-wrapper ratio-book card-img-top rounded-0 bg-light">
                                        <a href="{{ route('books.show', $book->id) }}">
                                            <img src="{{ $book->anh_bia ? asset('storage/' . $book->anh_bia) : 'https://via.placeholder.com/200x300' }}"
                                                class="lazy-img w-100 h-100 object-fit-cover" alt="{{ $book->tieu_de }}" />
                                        </a>
                                    </div>
                                    <div class="card-body d-flex flex-column px-3 py-3">
                                        <small class="text-muted text-uppercase fw-bold d-block mb-1"
                                            style="font-size: 0.7rem">Bìa thường</small>
                                        <h6 class="card-title fw-bold text-truncate mb-1">
                                            <a href="{{ route('books.show', $book->id) }}"
                                                class="text-dark text-decoration-none">{{ $book->tieu_de }}</a>
                                        </h6>
                                        <p class="text-muted small mb-2">{{ $book->author_name ?? 'Đang cập nhật' }}</p>
                                        <div class="mt-auto d-flex justify-content-between align-items-center">
                                            <h5 class="fw-bold text-danger mb-0">{{ number_format($book->gia_ban, 0, ',', '.') }} đ</h5>
                                            <form action="{{ route('cart.add') }}" method="POST" class="m-0">
                                                @csrf
                                                <input type="hidden" name="id_sach" value="{{ $book->id }}">
                                                <input type="hidden" name="so_luong" value="1">
                                                <button type="submit" class="btn btn-sm btn-dark rounded-0">Thêm vào giỏ</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Phân trang Laravel Pagination -->
                    <div class="mt-5 d-flex justify-content-center">
                        {{ $books->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
