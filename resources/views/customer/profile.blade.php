@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row">
            <!-- Sidebar Menu -->
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        @if ($user->anh_dai_dien_url)
                            <img src="{{ $user->anh_dai_dien_url }}"
                                class="rounded-circle mb-3 border object-fit-cover" width="100" height="100"
                                alt="Ảnh đại diện của {{ $user->ho_ten }}">
                        @else
                            <div class="rounded-circle mb-3 border bg-light d-inline-flex align-items-center justify-content-center"
                                style="width: 100px; height: 100px;" aria-label="Chưa có ảnh đại diện">
                                <i class="bi bi-person fs-1 text-secondary" aria-hidden="true"></i>
                            </div>
                        @endif
                        <h5 class="fw-bold">{{ $user->ho_ten }}</h5>
                        <p class="text-muted small">{{ $user->email }}</p>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('customer.profile') }}"
                            class="list-group-item list-group-item-action active fw-bold">
                            <i class="bi bi-person me-2"></i> Thông tin cá nhân
                        </a>
                        <a href="{{ route('customer.orders.index') }}" class="list-group-item list-group-item-action">
                            <i class="bi bi-box-seam me-2"></i> Lịch sử đơn hàng
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="list-group-item list-group-item-action text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Cập nhật thông tin -->
            <div class="col-md-9">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">Thông tin cá nhân</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Họ và tên</label>
                                    <input type="text" name="ho_ten" class="form-control" value="{{ $user->ho_ten }}"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" name="so_dien_thoai" class="form-control"
                                        value="{{ $user->so_dien_thoai }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email (Không thể thay đổi)</label>
                                <input type="email" class="form-control text-muted" value="{{ $user->email }}" readonly
                                    disabled>
                            </div>
                            <div class="mb-3">
                                <label for="anh_dai_dien" class="form-label">Ảnh đại diện</label>
                                <input id="anh_dai_dien" type="file" name="anh_dai_dien"
                                    class="form-control @error('anh_dai_dien') is-invalid @enderror"
                                    accept="image/jpeg,image/png,image/webp">
                                <div class="form-text">Chọn ảnh JPG, PNG hoặc WEBP, dung lượng tối đa 2 MB.</div>
                                @error('anh_dai_dien')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-dark px-4">Lưu thay đổi</button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">Đổi mật khẩu</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('customer.profile.password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu hiện tại</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Mật khẩu mới</label>
                                    <input type="password" name="new_password"
                                        class="form-control @error('new_password') is-invalid @enderror" required>
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Xác nhận mật khẩu mới</label>
                                    <input type="password" name="new_password_confirmation" class="form-control" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-outline-dark px-4">Đổi mật khẩu</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
