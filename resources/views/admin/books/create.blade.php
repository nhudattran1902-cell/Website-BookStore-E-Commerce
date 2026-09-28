@extends('admin.layouts.master')

@section('title', 'Thêm sách mới - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Thêm sách mới</h3>
                <p class="text-muted mb-0">Tạo thông tin sách mới vào thư viện hệ thống</p>
            </div>
            <a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tiêu đề sách <span class="text-danger">*</span></label>
                                <input type="text" name="tieu_de"
                                    class="form-control @error('tieu_de') is-invalid @enderror" value="{{ old('tieu_de') }}"
                                    required placeholder="Nhập tên sách...">
                                @error('tieu_de')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Thể loại <span class="text-danger">*</span></label>
                                    <select name="id_the_loai"
                                        class="form-select @error('id_the_loai') is-invalid @enderror" required>
                                        <option value="">-- Chọn thể loại --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('id_the_loai') == $category->id ? 'selected' : '' }}>
                                                {{ $category->ten_the_loai }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_the_loai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Nhà xuất bản</label>
                                    <select name="id_nha_xuat_ban" class="form-select">
                                        <option value="">-- Chọn nhà xuất bản --</option>
                                        @foreach ($publishers as $publisher)
                                            <option value="{{ $publisher->id }}"
                                                {{ old('id_nha_xuat_ban') == $publisher->id ? 'selected' : '' }}>
                                                {{ $publisher->ten_nxb }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Mô tả sách</label>
                                <textarea name="mo_ta" class="form-control" rows="5" placeholder="Nhập tóm tắt nội dung cuốn sách...">{{ old('mo_ta') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="gia_ban"
                                    class="form-control @error('gia_ban') is-invalid @enderror"
                                    value="{{ old('gia_ban') }}" step="1000" required placeholder="0">
                                @error('gia_ban')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Mã ISBN</label>
                                <input type="text" name="ma_isbn" class="form-control" value="{{ old('ma_isbn') }}"
                                    placeholder="978-3-16-148410-0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Năm xuất bản</label>
                                <input type="number" name="nam_xuat_ban" class="form-control"
                                    value="{{ old('nam_xuat_ban', date('Y')) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Ảnh bìa sách</label>
                                <input type="file" name="anh_bia" class="form-control" accept="image/*">
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="dang_hoat_dong" value="1"
                                    id="dang_hoat_dong" checked>
                                <label class="form-check-label fw-bold" for="dang_hoat_dong">Cho phép hiển thị/bán</label>
                            </div>

                            <button type="submit" class="btn btn-lime w-100 py-2 mt-2">
                                <i class="bi bi-save me-1"></i> Lưu thông tin sách
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
