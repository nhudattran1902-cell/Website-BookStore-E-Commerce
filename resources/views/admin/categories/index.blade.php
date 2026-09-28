@extends('admin.layouts.master')

@section('title', 'Quản lý Thể loại - BOOK & BOX')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Quản lý Thể loại</h1>
            <p class="page-subtitle">Danh mục phân loại sách trong cửa hàng.</p>
        </div>
        <button class="btn btn-success text-white" data-bs-toggle="modal" data-bs-target="#modalAddCategory">
            <i class="bi bi-plus-lg me-1"></i> Thêm thể loại
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    <div class="table-card-custom">
        <div class="table-responsive">
            <table class="table-custom w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên thể loại</th>
                        <th>Đường dẫn tĩnh (Slug)</th>
                        <th>Mô tả</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($danhSachTheLoai as $tl)
                        <tr>
                            <td class="fw-bold">#{{ $tl->id }}</td>
                            <td class="fw-semibold">{{ $tl->ten_the_loai }}</td>
                            <td><code>{{ $tl->duong_dan_tinh }}</code></td>
                            <td>{{ Str::limit($tl->mo_ta, 40) }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                    data-bs-target="#modalEditCategory{{ $tl->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('admin.categories.destroy', $tl->id) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa thể loại này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEditCategory{{ $tl->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('admin.categories.update', $tl->id) }}" method="POST"
                                    class="modal-content">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Sửa thể loại</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Tên thể loại</label>
                                            <input type="text" name="ten_the_loai" class="form-control"
                                                value="{{ $tl->ten_the_loai }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Mô tả</label>
                                            <textarea name="mo_ta" class="form-control" rows="3">{{ $tl->mo_ta }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Chưa có dữ liệu thể loại.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $danhSachTheLoai->links() }}
        </div>
    </div>

    <!-- Modal Add -->
    <div class="modal fade" id="modalAddCategory" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('admin.categories.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Thêm thể loại mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên thể loại</label>
                        <input type="text" name="ten_the_loai" class="form-control" placeholder="Nhập tên thể loại..."
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="mo_ta" class="form-control" rows="3" placeholder="Mô tả ngắn..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>
@endsection
