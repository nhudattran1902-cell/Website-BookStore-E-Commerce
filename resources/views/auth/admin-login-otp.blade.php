@extends('layouts.app')

@section('title', 'Xác minh đăng nhập - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="fw-bold text-center mb-3">Xác minh đăng nhập</h3>
                    <p class="text-center text-muted mb-4">
                        Nhập mã OTP đã gửi đến <strong>{{ $maskedEmail }}</strong> để hoàn tất đăng nhập nhân viên.
                        Mã có hiệu lực trong 5 phút.
                    </p>

                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    <form action="{{ route('admin.login.otp.verify') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="code" class="form-label fw-bold">Mã OTP</label>
                            <input
                                id="code"
                                type="text"
                                name="code"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                pattern="[0-9]{6}"
                                maxlength="6"
                                class="form-control text-center fs-4 @error('code') is-invalid @enderror"
                                aria-describedby="otp-help"
                                required
                                autofocus
                            >
                            <div id="otp-help" class="form-text">Mã gồm 6 chữ số. Nếu nhập sai 5 lần, bạn cần yêu cầu mã mới.</div>
                            @error('code')
                                <div id="otp-error" class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-dark w-100 py-2 fw-bold">Xác minh và đăng nhập</button>
                    </form>

                    <form action="{{ route('admin.login.otp.resend') }}" method="POST" class="mt-3 text-center">
                        @csrf
                        <button type="submit" class="btn btn-link">Gửi lại mã OTP</button>
                    </form>

                    <div class="text-center mt-2">
                        <a href="{{ route('login') }}">Quay lại đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
