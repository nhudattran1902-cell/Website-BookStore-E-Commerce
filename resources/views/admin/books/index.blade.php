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

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-custom">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3" style="width: 70px;">ID</th>
                                <th style="width: 80px;">Hình ảnh</th>
                                <th>Tiêu đề sách</th>
                                <th>Thể loại</th>
                                <th>Giá bán</th>
                                <th>Trạng thái</th>
                                <th class="text-end pe-3">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($books as $book)
                                <tr>
                                    <td class="ps-3 fw-bold">#{{ $book->id }}</td>
                                    <td>
                                        <img src="{{ $book->anh_bia ? asset('storage/' . $book->anh_bia) : asset('images/no-cover.jpg') }}"
                                            alt="{{ $book->tieu_de }}" class="rounded shadow-sm"
                                            style="width: 48px; height: 64px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-white">{{ $book->tieu_de }}</div>
                                        <small class="text-muted">ISBN: {{ $book->ma_isbn ?? 'Chưa cập nhật' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $book->theLoai->ten_the_loai ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-lime">
                                        {{ number_format($book->gia_ban, 0, ',', '.') }} đ
                                    </td>
                                    <td>
                                        @if ($book->dang_hoat_dong)
                                            <span class="badge bg-success">Đang bán</span>
                                        @else
                                            <span class="badge bg-danger">Ẩn/Ngừng</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('admin.books.edit', $book->id) }}"
                                            class="btn btn-sm btn-outline-info me-1">
                                            <i class="bi bi-pencil-square"></i> Sửa
                                        </a>
                                        <form action="{{ route('admin.books.destroy', $book->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Bạn có chắc muốn xóa cuốn sách này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Chưa có dữ liệu sách nào trong cơ sở dữ liệu.
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
