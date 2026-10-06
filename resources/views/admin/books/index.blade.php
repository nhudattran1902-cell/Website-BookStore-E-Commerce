@extends('admin.layouts.master')

@section('title', 'Quản lý Sách - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Quản lý kho sách</h3>
                <p class="text-muted mb-0">Danh sách các đầu sách hiện có trong hệ thống</p>
            </div>
            <a href="{{ route('admin.books.create') }}" class="btn btn-lime">
                <i class="bi bi-plus-lg me-1"></i> Thêm sách mới
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.books.index') }}" method="GET" class="card border mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6 col-xl-3">
                        <label for="search" class="form-label small fw-semibold">Tiêu đề sách</label>
                        <input id="search" type="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Nhập tên sách...">
                    </div>
                    <div class="col-md-6 col-xl-2">
                        <label for="the_loai" class="form-label small fw-semibold">Thể loại</label>
                        <select id="the_loai" name="the_loai" class="form-select form-select-sm">
                            <option value="">Tất cả thể loại</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('the_loai') === (string) $category->id)>{{ $category->ten_the_loai }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-xl-1">
                        <label for="gia_tu" class="form-label small fw-semibold">Giá từ</label>
                        <input id="gia_tu" type="number" name="gia_tu" value="{{ request('gia_tu') }}" min="0" step="1000" class="form-control form-control-sm" placeholder="Tối thiểu">
                    </div>
                    <div class="col-6 col-xl-1">
                        <label for="gia_den" class="form-label small fw-semibold">Giá đến</label>
                        <input id="gia_den" type="number" name="gia_den" value="{{ request('gia_den') }}" min="0" step="1000" class="form-control form-control-sm" placeholder="Tối đa">
                    </div>
                    <div class="col-md-4 col-xl-2">
                        <label for="ton_kho" class="form-label small fw-semibold">Tồn kho</label>
                        <select id="ton_kho" name="ton_kho" class="form-select form-select-sm">
                            <option value="">Tất cả</option>
                            <option value="available" @selected(request('ton_kho') === 'available')>Còn hàng (&gt; 0)</option>
                            <option value="low" @selected(request('ton_kho') === 'low')>Sắp hết (&lt; 5)</option>
                            <option value="out" @selected(request('ton_kho') === 'out')>Hết hàng (= 0)</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-xl-1">
                        <label for="trang_thai" class="form-label small fw-semibold">Kinh doanh</label>
                        <select id="trang_thai" name="trang_thai" class="form-select form-select-sm">
                            <option value="">Tất cả</option>
                            <option value="active" @selected(request('trang_thai') === 'active')>Đang bán</option>
                            <option value="inactive" @selected(request('trang_thai') === 'inactive')>Ngừng bán</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-xl-2">
                        <label for="sort_gia" class="form-label small fw-semibold">Sắp xếp giá</label>
                        <select id="sort_gia" name="sort_gia" class="form-select form-select-sm">
                            <option value="">Mới cập nhật</option>
                            <option value="asc" @selected(request('sort_gia') === 'asc')>Giá tăng dần</option>
                            <option value="desc" @selected(request('sort_gia') === 'desc')>Giá giảm dần</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary btn-sm">Xóa bộ lọc</a>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Lọc dữ liệu</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-custom">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3" style="width: 70px;">ID</th>
                                <th style="width: 80px;">Hình ảnh</th>
                                <th>Tiêu đề sách</th>
                                <th>Tác giả</th>
                                <th>Thể loại</th>
                                <th>Giá bán</th>
                                <th>Tồn kho</th>
                                <th>Trạng thái</th>
                                <th class="text-end pe-3">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($books as $book)
                                @php
                                    $soLuongTon = $book->khoHang->so_luong ?? ($book->khoHang->so_luong_ton ?? 0);
                                @endphp
                                <tr>
                                    <td class="ps-3 fw-bold">#{{ $book->id }}</td>
                                    <td>
                                        <img src="{{ $book->anh_bia_url ?: asset('images/no-cover.jpg') }}"
                                            alt="{{ $book->tieu_de }}" class="rounded shadow-sm"
                                            style="width: 48px; height: 64px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $book->tieu_de }}</div>
                                        <small class="text-muted">ISBN: {{ $book->ma_isbn ?? 'Chưa cập nhật' }}</small>
                                    </td>
                                    <td>{{ $book->tacGia->pluck('ten_tac_gia')->implode(', ') ?: 'Đang cập nhật' }}</td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $book->theLoai->ten_the_loai ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-lime">{{ number_format($book->gia_khuyen_mai ?? $book->gia_ban, 0, ',', '.') }} đ</div>
                                        @if ($book->gia_khuyen_mai)
                                            <small class="text-muted text-decoration-line-through">{{ number_format($book->gia_ban, 0, ',', '.') }} đ</small>
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Ưu đãi</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $soLuongTon === 0 ? 'bg-danger-subtle text-danger-emphasis' : ($soLuongTon < 5 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success-emphasis') }}">
                                            <i class="bi {{ $soLuongTon === 0 ? 'bi-exclamation-triangle' : 'bi-box-seam' }} me-1"></i>{{ $soLuongTon }} cuốn
                                        </span>
                                    </td>
                                    <td>
                                        @if ($book->dang_hoat_dong)
                                            <span class="badge bg-success">Đang bán</span>
                                        @else
                                            <span class="badge bg-danger">Ẩn/Ngừng</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- Nút Quản lý Trang đọc thử (Bổ sung mới) --}}
                                            <a href="{{ route('admin.books.pages.index', $book->id) }}"
                                                class="btn btn-outline-info" title="Quản lý trang đọc thử">
                                                <i class="bi bi-book"></i> Đọc thử
                                            </a>

                                            {{-- Nút Nhập kho --}}
                                            <a href="{{ route('admin.inventory.index', ['id_sach' => $book->id]) }}"
                                                class="btn btn-outline-warning" title="Cập nhật kho">
                                                <i class="bi bi-box-arrow-in-down"></i> Kho
                                            </a>

                                            {{-- Nút Chỉnh sửa sách --}}
                                            <a href="{{ route('admin.books.edit', $book->id) }}"
                                                class="btn btn-outline-primary" title="Sửa thông tin sách">
                                                <i class="bi bi-pencil-square"></i> Sửa
                                            </a>

                                            {{-- Nút Xóa sách --}}
                                            <form action="{{ route('admin.books.destroy', $book->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Bạn có chắc muốn xóa cuốn sách này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Xóa sách">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        Không có đầu sách phù hợp bộ lọc.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if (method_exists($books, 'links'))
                <div class="card-footer bg-transparent border-0 d-flex justify-content-end pt-3">
                    {{ $books->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
