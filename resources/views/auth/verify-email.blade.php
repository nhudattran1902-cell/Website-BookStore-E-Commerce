@extends('layouts.app')

@section('title', 'Xác minh đăng ký - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card border-0 shadow-sm p-4 text-center">
                    <h3 class="fw-bold mb-3">Xác minh email đăng ký</h3>
                    <p>Nhập mã OTP 6 số đã gửi đến email để hoàn tất đăng ký tài khoản.</p>

                    @if (session('success') || session('status'))
                        <div class="alert alert-success">
                            {{ session('success') ?? session('status') }}
                        </div>
                    @endif

                    <form action="{{ route('registration.otp.verify') }}" method="POST" class="mb-3">
                        @csrf
                        <div class="mb-3 text-start">
                            <label for="code" class="form-label fw-bold">Mã OTP</label>
                            <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}"
                                maxlength="6" autocomplete="one-time-code" required
                                class="form-control @error('code') is-invalid @enderror">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-dark">Xác minh tài khoản</button>
                    </form>

                    <form action="{{ route('registration.otp.resend') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-dark">Gửi lại mã OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
