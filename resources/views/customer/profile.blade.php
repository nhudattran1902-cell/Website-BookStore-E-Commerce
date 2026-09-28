@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row">
            <!-- Sidebar Menu -->
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <img src="{{ asset('images/avatar.png') }}" class="rounded-circle mb-3 border" width="100"
                            height="100" alt="Avatar">
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
                        <form action="{{ route('customer.profile.update') }}" method="POST">
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
