@extends('layouts.app')

@section('title', 'Đăng ký tài khoản - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="fw-bold text-center mb-4">Đăng ký tài khoản</h3>

                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="ho_ten" class="form-control @error('ho_ten') is-invalid @enderror"
                                value="{{ old('ho_ten') }}" required placeholder="Nhập họ và tên">
                            @error('ho_ten')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Địa chỉ Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" required placeholder="email@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Số điện thoại</label>
                            <input type="text" name="so_dien_thoai" class="form-control"
                                value="{{ old('so_dien_thoai') }}" placeholder="Nhập số điện thoại">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="mat_khau"
                                class="form-control @error('mat_khau') is-invalid @enderror" required
                                placeholder="Tối thiểu 6 ký tự">
                            @error('mat_khau')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="mat_khau_confirmation" class="form-control" required
                                placeholder="Nhập lại mật khẩu">
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-2 fw-bold text-uppercase">Tạo tài khoản</button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">Đã có tài khoản? <a href="{{ route('login') }}"
                                class="fw-bold text-decoration-none">Đăng nhập</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
