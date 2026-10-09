@extends('admin.layouts.master')

@section('title', 'Chỉnh sửa Sách - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Chỉnh sửa sách #{{ $book->id }}</h3>
                <p class="text-muted mb-0">Cập nhật chi tiết nội dung và thuộc tính sách</p>
            </div>
            <a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <form action="{{ route('admin.books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tiêu đề sách <span class="text-danger">*</span></label>
                                <input type="text" name="tieu_de"
                                    class="form-control @error('tieu_de') is-invalid @enderror"
                                    value="{{ old('tieu_de', $book->tieu_de) }}" required>
                                @error('tieu_de')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Thể loại <span class="text-danger">*</span></label>
                                    <select name="id_the_loai"
                                        class="form-select @error('id_the_loai') is-invalid @enderror" required>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('id_the_loai', $book->id_the_loai) == $category->id ? 'selected' : '' }}>
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
                                                {{ old('id_nha_xuat_ban', $book->id_nha_xuat_ban) == $publisher->id ? 'selected' : '' }}>
                                                {{ $publisher->ten_nxb }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <h5 class="fw-bold mt-4 mb-3">Thông số ấn bản</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="loai_bia" class="form-label fw-bold">Loại bìa</label>
                                    <select id="loai_bia" name="loai_bia" class="form-select @error('loai_bia') is-invalid @enderror">
                                        <option value="">-- Chọn loại bìa --</option>
                                        <option value="bia_mem" @selected(old('loai_bia', $book->loai_bia) === 'bia_mem')>Bìa mềm</option>
                                        <option value="bia_cung" @selected(old('loai_bia', $book->loai_bia) === 'bia_cung')>Bìa cứng</option>
                                    </select>
                                    @error('loai_bia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="so_trang" class="form-label fw-bold">Số trang</label>
                                    <input id="so_trang" type="number" name="so_trang" min="1" max="100000"
                                        value="{{ old('so_trang', $book->so_trang) }}" class="form-control @error('so_trang') is-invalid @enderror">
                                    @error('so_trang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ngon_ngu" class="form-label fw-bold">Ngôn ngữ</label>
                                    <input id="ngon_ngu" type="text" name="ngon_ngu" maxlength="100"
                                        value="{{ old('ngon_ngu', $book->ngon_ngu) }}" class="form-control @error('ngon_ngu') is-invalid @enderror"
                                        placeholder="Ví dụ: Tiếng Việt">
                                    @error('ngon_ngu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lan_tai_ban" class="form-label fw-bold">Lần tái bản</label>
                                    <input id="lan_tai_ban" type="number" name="lan_tai_ban" min="1" max="65535"
                                        value="{{ old('lan_tai_ban', $book->lan_tai_ban) }}" class="form-control @error('lan_tai_ban') is-invalid @enderror">
                                    @error('lan_tai_ban')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="khoi_luong_gram" class="form-label fw-bold">Khối lượng (gram)</label>
                                    <input id="khoi_luong_gram" type="number" name="khoi_luong_gram" min="1" max="1000000"
                                        value="{{ old('khoi_luong_gram', $book->khoi_luong_gram) }}" class="form-control @error('khoi_luong_gram') is-invalid @enderror">
                                    @error('khoi_luong_gram')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nha_cung_cap" class="form-label fw-bold">Nhà cung cấp</label>
                                    <input id="nha_cung_cap" type="text" name="nha_cung_cap" maxlength="255"
                                        value="{{ old('nha_cung_cap', $book->nha_cung_cap) }}" class="form-control @error('nha_cung_cap') is-invalid @enderror">
                                    @error('nha_cung_cap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="chieu_rong_mm" class="form-label fw-bold">Rộng (mm)</label>
                                    <input id="chieu_rong_mm" type="number" name="chieu_rong_mm" min="0.1" max="5000" step="0.1"
                                        value="{{ old('chieu_rong_mm', $book->chieu_rong_mm) }}" class="form-control @error('chieu_rong_mm') is-invalid @enderror">
                                    @error('chieu_rong_mm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="chieu_cao_mm" class="form-label fw-bold">Cao (mm)</label>
                                    <input id="chieu_cao_mm" type="number" name="chieu_cao_mm" min="0.1" max="5000" step="0.1"
                                        value="{{ old('chieu_cao_mm', $book->chieu_cao_mm) }}" class="form-control @error('chieu_cao_mm') is-invalid @enderror">
                                    @error('chieu_cao_mm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="do_day_mm" class="form-label fw-bold">Độ dày (mm)</label>
                                    <input id="do_day_mm" type="number" name="do_day_mm" min="0.1" max="1000" step="0.1"
                                        value="{{ old('do_day_mm', $book->do_day_mm) }}" class="form-control @error('do_day_mm') is-invalid @enderror">
                                    @error('do_day_mm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Mô tả sách</label>
                                <textarea name="mo_ta" class="form-control" rows="5">{{ old('mo_ta', $book->mo_ta) }}</textarea>
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
                                    value="{{ old('gia_ban', $book->gia_ban) }}" step="1000" required>
                                @error('gia_ban')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Giá ưu đãi tuần này (VNĐ)</label>
                                <input type="number" name="gia_khuyen_mai"
                                    class="form-control @error('gia_khuyen_mai') is-invalid @enderror"
                                    value="{{ old('gia_khuyen_mai', $book->gia_khuyen_mai) }}" min="1" step="1000" placeholder="Để trống nếu không giảm giá">
                                @error('gia_khuyen_mai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Giá ưu đãi phải thấp hơn giá bán. Để trống để gỡ khỏi ưu đãi tuần.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Mã ISBN</label>
                                <input type="text" name="ma_isbn" class="form-control"
                                    value="{{ old('ma_isbn', $book->ma_isbn) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Năm xuất bản</label>
                                <input type="number" name="nam_xuat_ban" class="form-control"
                                    value="{{ old('nam_xuat_ban', $book->nam_xuat_ban) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Ảnh bìa hiện tại</label>
                                <div class="mb-2">
                                    <img id="book-cover-preview"
                                        src="{{ $book->anh_bia_url ?: asset('images/no-cover.jpg') }}"
                                        class="rounded img-thumbnail" style="height: 120px; object-fit: cover;">
                                    <label for="book-cover-input" class="btn btn-sm btn-outline-primary ms-2">Thay đổi ảnh bìa</label>
                                </div>
                                <input type="file" name="anh_bia" id="book-cover-input"
                                    class="form-control @error('anh_bia') is-invalid @enderror" accept="image/*"
                                    data-image-preview="book-cover-preview">
                                @error('anh_bia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="dang_hoat_dong" value="1"
                                    id="dang_hoat_dong" {{ $book->dang_hoat_dong ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="dang_hoat_dong">Hiển thị/bán</label>
                            </div>

                            <button type="submit" class="btn btn-lime w-100 py-2 mt-2">
                                <i class="bi bi-save me-1"></i> Cập nhật thay đổi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
            });
        });
    </script>
@endpush
