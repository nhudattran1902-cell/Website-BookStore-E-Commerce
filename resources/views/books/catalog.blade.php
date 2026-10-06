@extends('layouts.app')

@section('title', 'Cửa hàng sách - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <p class="text-uppercase text-muted small fw-bold mb-1">Danh mục sách</p>
                <h1 class="h2 fw-bold mb-0">Chủ đề và bộ truyện</h1>
            </div>
            <form action="{{ request()->is('book') ? route('books.catalog') : route('books.index') }}" method="GET"
                class="d-flex align-items-center gap-2">
                @if (request('the_loai'))
                    <input type="hidden" name="the_loai" value="{{ request('the_loai') }}">
                @endif
                <label for="sort" class="text-muted small mb-0">Sắp xếp giá:</label>
                <select name="sort" id="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Tên bộ</option>
                    <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
                    <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
                </select>
            </form>
        </div>

        <nav class="d-flex flex-wrap gap-2 mb-5" aria-label="Chủ đề sách">
            <a href="{{ route('books.index') }}" class="btn btn-sm {{ !request('the_loai') ? 'btn-dark' : 'btn-outline-dark' }}">
                Tất cả chủ đề
            </a>
            @foreach ($topics as $topic)
                <a href="#topic-{{ $topic['id'] }}" class="btn btn-sm btn-outline-dark">
                    {{ $topic['name'] }} <span class="text-muted">{{ $topic['book_count'] }}</span>
                </a>
            @endforeach
        </nav>

        @forelse ($topics as $topic)
            <section id="topic-{{ $topic['id'] }}" class="mb-5">
                <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2 border-bottom pb-2 mb-4">
                    <h2 class="h3 fw-bold mb-0">{{ $topic['name'] }}</h2>
                    <span class="text-muted small">{{ $topic['book_count'] }} cuốn sách</span>
                </div>

                @foreach ($topic['series'] as $series)
                    <section class="mb-5" aria-label="Bộ truyện {{ $series['name'] }}">
                        <div class="d-flex align-items-baseline gap-2 mb-3">
                            <h3 class="h5 fw-bold mb-0">{{ $series['name'] }}</h3>
                            <span class="text-muted small">{{ $series['volumes']->count() }} tập/ấn phẩm</span>
                        </div>

                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                            @foreach ($series['volumes'] as $volume)
                                @php
                                    $stockQuantity = $volume->khoHang->so_luong_ton ?? 0;
                                @endphp
                                <div class="col">
                                    <article class="card book-card h-100 shadow-sm border-0">
                                        <a href="{{ route('books.show', $volume->id) }}" class="img-wrapper ratio-book bg-light">
                                            <img src="{{ $volume->anh_bia_url ?: 'https://via.placeholder.com/200x300' }}"
                                                class="lazy-img w-100 h-100 object-fit-cover" alt="{{ $volume->tieu_de }}" loading="lazy">
                                        </a>
                                        <div class="card-body d-flex flex-column px-3 py-3">
                                            <span class="text-muted small mb-1">{{ $volume->getAttribute('catalog_volume_label') }}</span>
                                            <h4 class="h6 fw-bold mb-2">
                                                <a href="{{ route('books.show', $volume->id) }}" class="text-dark text-decoration-none">
                                                    {{ $volume->getAttribute('catalog_volume_label') }}
                                                </a>
                                            </h4>
                                            <p class="text-muted small mb-2">
                                                {{ $volume->tacGia->pluck('ten_tac_gia')->implode(', ') ?: 'Đang cập nhật' }}
                                            </p>
                                            <div class="mb-3">
                                                <span class="stock-status {{ $stockQuantity > 0 ? 'stock-status--available' : 'stock-status--unavailable' }}">
                                                    <span class="stock-status__dot" aria-hidden="true"></span>
                                                    <span>{{ $stockQuantity > 0 ? 'Còn hàng' : 'Hết hàng' }}</span>
                                                    @if ($stockQuantity > 0)
                                                        <span class="stock-status__quantity">{{ number_format($stockQuantity) }} cuốn</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="mt-auto d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                <strong class="text-danger">{{ number_format($volume->gia_ban, 0, ',', '.') }} đ</strong>
                                                @if ($stockQuantity > 0)
                                                    <form action="{{ route('cart.add') }}" method="POST" class="m-0" data-cart-form>
                                                        @csrf
                                                        <input type="hidden" name="id_sach" value="{{ $volume->id }}">
                                                        <input type="hidden" name="so_luong" value="1">
                                                        <button type="submit" class="btn btn-sm btn-dark" data-cart-submit>Thêm vào giỏ</button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-dark" disabled>Hết hàng</button>
                                                @endif
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </section>
        @empty
            <div class="alert alert-warning">Chưa có sách trong chủ đề này.</div>
        @endforelse
    </div>
@endsection