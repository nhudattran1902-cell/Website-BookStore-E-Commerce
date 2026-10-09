@extends('layouts.app')

@section('title', 'Đăng nhập - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="fw-bold text-center mb-4">Đăng nhập</h3>

                    @if (session('error'))
                        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Địa chỉ Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" required placeholder="email@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mật khẩu</label>
                            <input type="password" name="mat_khau"
                                class="form-control @error('mat_khau') is-invalid @enderror" required placeholder="••••••••">
                            @error('mat_khau')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check d-flex justify-content-between">
                            <div>
                                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Ghi nhớ đăng nhập (không áp dụng cho nhân viên/quản trị)</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-2 fw-bold text-uppercase">Đăng nhập</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
                    </div>

                    <div class="text-center mt-4">
                        <p class="mb-0">Chưa có tài khoản? <a href="{{ route('register') }}"
                                class="fw-bold text-decoration-none">Đăng ký ngay</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
