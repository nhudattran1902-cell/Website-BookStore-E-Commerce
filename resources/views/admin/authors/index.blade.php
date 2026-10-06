@extends('admin.layouts.master')

@section('title', 'Quản lý Tác giả - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Quản lý Tác giả</h3>
                <p class="text-muted mb-0">Danh sách các tác giả sách trong hệ thống</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAuthorModal">
                <i class="bi bi-plus-lg me-1"></i> Thêm tác giả mới
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Bảng danh sách --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3" style="width: 70px;">ID</th>
                                <th style="width: 80px;">Ảnh</th>
                                <th>Tên tác giả</th>
                                <th>Tiểu sử</th>
                                <th>Số tác phẩm</th>
                                <th class="text-end pe-3">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($authors as $author)
                                <tr>
                                    <td class="ps-3 fw-bold">#{{ $author->id }}</td>
                                    <td>
                                        <img src="{{ $author->anh_dai_dien ? asset('storage/' . $author->anh_dai_dien) : asset('images/no-avatar.jpg') }}"
                                            alt="{{ $author->ten_tac_gia }}" class="rounded-circle shadow-sm"
                                            style="width: 48px; height: 48px; object-fit: cover;">
                                    </td>
                                    <td class="fw-bold">{{ $author->ten_tac_gia }}</td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($author->tieu_su ?? 'Chưa có tiểu sử', 60) }}</small>
                                    </td>
                                    <td>
                                        {{-- Sửa saches_count thành sach_count --}}
                                        <span class="badge bg-info text-dark">{{ $author->sach_count ?? 0 }} đầu sách</span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <button class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal"
                                            data-bs-target="#editAuthorModal{{ $author->id }}">
                                            <i class="bi bi-pencil-square"></i> Sửa
                                        </button>

                                        <form action="{{ route('admin.authors.destroy', $author->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Bạn có chắc chắn muốn xóa tác giả này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Modal Sửa Tác Giả --}}
                                <div class="modal fade" id="editAuthorModal{{ $author->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.authors.update', $author->id) }}" method="POST"
                                                enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Cập nhật Tác giả #{{ $author->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Tên tác giả <span class="text-danger">*</span></label>
                                                        <input type="text" name="ten_tac_gia" class="form-control"
                                                            value="{{ old('ten_tac_gia', $author->ten_tac_gia) }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Ảnh đại diện</label>
                                                        <input type="file" id="author-image-input-{{ $author->id }}" name="anh_dai_dien" class="form-control" accept="image/*"
                                                            data-image-preview="author-image-preview-{{ $author->id }}">
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block mb-1">Ảnh hiện tại:</small>
                                                            <img id="author-image-preview-{{ $author->id }}"
                                                                src="{{ $author->anh_dai_dien ? asset('storage/' . $author->anh_dai_dien) : asset('images/no-avatar.jpg') }}"
                                                                class="rounded" style="height: 60px; object-fit: cover;">
                                                            <label for="author-image-input-{{ $author->id }}" class="btn btn-sm btn-outline-primary ms-2">Thay đổi ảnh</label>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Tiểu sử</label>
                                                        <textarea name="tieu_su" class="form-control" rows="4">{{ old('tieu_su', $author->tieu_su) }}</textarea>
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
                                    <td colspan="6" class="text-center py-4 text-muted">Chưa có dữ liệu tác giả.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if (method_exists($authors, 'links'))
                <div class="card-footer bg-transparent border-0 d-flex justify-content-end pt-3">
                    {{ $authors->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Thêm Tác Giả Mới --}}
    <div class="modal fade" id="createAuthorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.authors.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Thêm Tác giả mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên tác giả <span class="text-danger">*</span></label>
                            <input type="text" name="ten_tac_gia" class="form-control"
                                value="{{ old('ten_tac_gia') }}" required placeholder="Nhập tên tác giả...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ảnh đại diện</label>
                            <input type="file" name="anh_dai_dien" class="form-control" accept="image/*"
                                data-image-preview="new-author-image-preview">
                            <img id="new-author-image-preview" class="rounded mt-2 d-none"
                                style="height: 60px; object-fit: cover;" alt="Xem trước ảnh tác giả">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiểu sử</label>
                            <textarea name="tieu_su" class="form-control" rows="4" placeholder="Nhập tóm tắt tiểu sử tác giả...">{{ old('tieu_su') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Thêm mới</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-image-preview]').forEach((input) => {
            input.addEventListener('change', () => {
                const image = document.getElementById(input.dataset.imagePreview);
                const file = input.files[0];

                if (!image || !file) {
                    return;
                }

                if (image.dataset.previewUrl) {
                    URL.revokeObjectURL(image.dataset.previewUrl);
                }

                image.dataset.previewUrl = URL.createObjectURL(file);
                image.src = image.dataset.previewUrl;
                image.classList.remove('d-none');
            });
        });
    </script>
@endpush