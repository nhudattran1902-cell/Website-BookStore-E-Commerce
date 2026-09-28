@extends('admin.layouts.master')
@section('title', 'Quản lý Nhà xuất bản')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Quản lý Nhà xuất bản</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalThemNXB">
            <i class="bi bi-plus-lg me-1"></i> Thêm NXB
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
                            <th>Tên nhà xuất bản</th>
                            <th>Địa chỉ</th>
                            <th>Website</th>
                            <th class="text-end pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($publishers as $nxb)
                            <tr>
                                <td class="ps-3">{{ $nxb->id }}</td>
                                <td class="fw-bold">{{ $nxb->ten_nxb }}</td>
                                <td>{{ $nxb->dia_chi ?? '—' }}</td>
                                <td>
                                    @if ($nxb->website)
                                        <a href="{{ $nxb->website }}" target="_blank" class="text-muted small">{{ $nxb->website }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-outline-primary me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalSuaNXB{{ $nxb->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.publishers.destroy', $nxb->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Xóa nhà xuất bản này?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Sửa -->
                            <div class="modal fade" id="modalSuaNXB{{ $nxb->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Sửa nhà xuất bản</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.publishers.update', $nxb->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Tên NXB <span class="text-danger">*</span></label>
                                                    <input type="text" name="ten_nxb" class="form-control" value="{{ $nxb->ten_nxb }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Địa chỉ</label>
                                                    <input type="text" name="dia_chi" class="form-control" value="{{ $nxb->dia_chi }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Website</label>
                                                    <input type="url" name="website" class="form-control" value="{{ $nxb->website }}">
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
                                <td colspan="5" class="text-center py-4 text-muted">Chưa có nhà xuất bản nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $publishers->links() }}</div>

    <!-- Modal Thêm mới -->
    <div class="modal fade" id="modalThemNXB" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm nhà xuất bản mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.publishers.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tên NXB <span class="text-danger">*</span></label>
                            <input type="text" name="ten_nxb" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="dia_chi" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control" placeholder="https://...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Thêm NXB</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

