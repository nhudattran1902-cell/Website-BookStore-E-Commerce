@extends('admin.layouts.master')
@section('title', 'Quản lý Tác giả')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Quản lý Tác giả</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalThemTacGia">
            <i class="bi bi-plus-lg me-1"></i> Thêm tác giả
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Tên tác giả</th>
                            <th>Quốc tịch</th>
                            <th class="text-end pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($authors as $author)
                            <tr>
                                <td class="ps-3">{{ $author->id }}</td>
                                <td class="fw-bold">{{ $author->ten_tac_gia }}</td>
                                <td>{{ $author->quoc_tich ?? '—' }}</td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-outline-primary me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalSuaTacGia{{ $author->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.authors.destroy', $author->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Xóa tác giả này?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Sửa -->
                            <div class="modal fade" id="modalSuaTacGia{{ $author->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Sửa tác giả</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.authors.update', $author->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Tên tác giả <span class="text-danger">*</span></label>
                                                    <input type="text" name="ten_tac_gia" class="form-control" value="{{ $author->ten_tac_gia }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Quốc tịch</label>
                                                    <input type="text" name="quoc_tich" class="form-control" value="{{ $author->quoc_tich }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Tiểu sử</label>
                                                    <textarea name="tieu_su" class="form-control" rows="3">{{ $author->tieu_su }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Chưa có tác giả nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $authors->links() }}</div>

    <!-- Modal Thêm mới -->
    <div class="modal fade" id="modalThemTacGia" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm tác giả mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.authors.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tên tác giả <span class="text-danger">*</span></label>
                            <input type="text" name="ten_tac_gia" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quốc tịch</label>
                            <input type="text" name="quoc_tich" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tiểu sử</label>
                            <textarea name="tieu_su" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Thêm tác giả</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

