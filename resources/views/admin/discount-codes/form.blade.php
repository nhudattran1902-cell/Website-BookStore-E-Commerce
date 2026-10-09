@extends('admin.layouts.master')

@section('title', $discountCode ? 'Sửa mã giảm giá' : 'Tạo mã giảm giá')

@section('content')
    <div class="page-header mb-4">
        <h1 class="page-title">{{ $discountCode ? 'Sửa mã giảm giá' : 'Tạo mã giảm giá' }}</h1>
        <p class="page-subtitle">Thiết lập điều kiện và thời hạn áp dụng mã cho đơn hàng.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-card-custom p-4">
        <form action="{{ $discountCode ? route('admin.discount-codes.update', $discountCode) : route('admin.discount-codes.store') }}" method="POST">
            @csrf
            @if ($discountCode)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="ma_code" class="form-label">Mã giảm giá</label>
                    <input id="ma_code" name="ma_code" class="form-control" maxlength="50" required
                        value="{{ old('ma_code', $discountCode?->ma_code) }}" placeholder="Ví dụ: SACH20">
                    <small class="text-muted">Chỉ dùng chữ không dấu, số, dấu gạch ngang hoặc gạch dưới.</small>
                </div>
                <div class="col-md-6">
                    <label for="loai_giam" class="form-label">Hình thức giảm</label>
                    <select id="loai_giam" name="loai_giam" class="form-select" required>
                        <option value="phan_tram" @selected(old('loai_giam', $discountCode?->loai_giam) === 'phan_tram')>Phần trăm</option>
                        <option value="so_tien_co_dinh" @selected(old('loai_giam', $discountCode?->loai_giam) === 'so_tien_co_dinh')>Số tiền cố định</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="gia_tri" class="form-label">Giá trị giảm</label>
                    <input id="gia_tri" name="gia_tri" type="number" class="form-control" min="1" required
                        value="{{ old('gia_tri', $discountCode?->gia_tri) }}">
                    <small class="text-muted">Phần trăm tối đa 100; số tiền tính bằng đồng.</small>
                </div>
                <div class="col-md-4">
                    <label for="gia_tri_toi_da" class="form-label">Giảm tối đa (đồng, tùy chọn)</label>
                    <input id="gia_tri_toi_da" name="gia_tri_toi_da" type="number" class="form-control" min="1"
                        value="{{ old('gia_tri_toi_da', $discountCode?->gia_tri_toi_da) }}">
                    <small class="text-muted">Chỉ áp dụng với mã giảm theo phần trăm.</small>
                </div>
                <div class="col-md-4">
                    <label for="don_toi_thieu" class="form-label">Đơn tối thiểu (đồng)</label>
                    <input id="don_toi_thieu" name="don_toi_thieu" type="number" class="form-control" min="0" required
                        value="{{ old('don_toi_thieu', $discountCode?->don_toi_thieu ?? 0) }}">
                </div>
                <div class="col-md-4">
                    <label for="gioi_han_luot" class="form-label">Giới hạn lượt dùng</label>
                    <input id="gioi_han_luot" name="gioi_han_luot" type="number" class="form-control" min="1"
                        value="{{ old('gioi_han_luot', $discountCode?->gioi_han_luot) }}" placeholder="Để trống nếu không giới hạn">
                </div>
                <div class="col-md-4">
                    <label for="ngay_bat_dau" class="form-label">Bắt đầu</label>
                    <input id="ngay_bat_dau" name="ngay_bat_dau" type="datetime-local" class="form-control"
                        value="{{ old('ngay_bat_dau', $discountCode?->ngay_bat_dau?->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-4">
                    <label for="ngay_het_han" class="form-label">Hết hạn</label>
                    <input id="ngay_het_han" name="ngay_het_han" type="datetime-local" class="form-control"
                        value="{{ old('ngay_het_han', $discountCode?->ngay_het_han?->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-12">
                    <input type="hidden" name="dang_hoat_dong" value="0">
                    <div class="form-check">
                        <input id="dang_hoat_dong" name="dang_hoat_dong" type="checkbox" value="1" class="form-check-input"
                            @checked(old('dang_hoat_dong', $discountCode?->dang_hoat_dong ?? true))>
                        <label for="dang_hoat_dong" class="form-check-label">Đang hoạt động</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-success text-white">{{ $discountCode ? 'Lưu thay đổi' : 'Tạo mã' }}</button>
                <a href="{{ route('admin.discount-codes.index') }}" class="btn btn-outline-secondary">Quay lại</a>
            </div>
        </form>
    </div>
@endsection
