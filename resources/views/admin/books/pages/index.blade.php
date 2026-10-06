@extends('admin.layouts.master')

@section('title', 'Quản lý Trang đọc thử - ' . $book->tieu_de)

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Trang đọc thử: {{ $book->tieu_de }}</h3>
                <p class="text-muted mb-0">Tải lên và quản lý danh sách hình ảnh các trang đọc thử cho khách hàng</p>
            </div>
            <a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách sách
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            {{-- Form tải lên trang đọc thử mới --}}
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold">
                        <i class="bi bi-cloud-upload me-1"></i> Thêm trang đọc thử mới
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.books.pages.store', $book->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            {{-- Hiển thị thông báo lỗi validation --}}
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show p-2 small mb-3" role="alert">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label fw-bold">Số trang <span class="text-danger">*</span></label>
                                <input type="number" name="so_trang" class="form-control" min="1"
                                    value="{{ old('so_trang', ($book->trangSach->max('so_trang') ?? 0) + 1) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Chọn hình ảnh trang <span
                                        class="text-danger">*</span></label>
                                {{-- Đảm bảo name là anh_trang để khớp với BookPageController --}}
                                <input type="file" name="anh_trang" class="form-control" accept="image/*" required>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="cho_phep_doc_thu" value="1"
                                    id="cho_phep_doc_thu" checked>
                                <label class="form-check-label fw-bold" for="cho_phep_doc_thu">Cho phép đọc thử công
                                    khai</label>
                            </div>

                            <button type="submit" class="btn btn-lime w-100 fw-bold">
                                <i class="bi bi-plus-circle me-1"></i> Tải trang lên
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Danh sách trang đọc thử hiện có --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold">
                        <i class="bi bi-images me-1"></i> Các trang đọc thử hiện tại ({{ $book->trangSach->count() }})
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-2 row-cols-md-3 g-3">
                            @forelse($book->trangSach->sortBy('so_trang') as $trang)
                                <div class="col">
                                    <div class="card h-100 border shadow-sm">
                                        <img src="{{ $trang->duong_dan_anh_url }}"
                                            class="card-img-top object-fit-cover" style="height: 220px;"
                                            alt="Trang {{ $trang->so_trang }}">
                                        <div class="card-body p-2 text-center">
                                            <div class="fw-bold mb-1">Trang {{ $trang->so_trang }}</div>
                                            @if ($trang->cho_phep_doc_thu)
                                                <span class="badge bg-success mb-2">Hiển thị</span>
                                            @else
                                                <span class="badge bg-secondary mb-2">Ẩn</span>
                                            @endif
                                            <form
                                                action="{{ route('admin.books.pages.destroy', [$book->id, $trang->id]) }}"
                                                method="POST"
                                                onsubmit="return confirm('Bạn có chắc muốn xóa trang này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                    <i class="bi bi-trash"></i> Xóa trang
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5 text-muted">
                                    Cuốn sách này chưa có hình ảnh trang đọc thử nào. Hãy dùng form bên cạnh để tải ảnh lên.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
