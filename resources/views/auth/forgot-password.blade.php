@extends('layouts.app')

@section('title', 'Quên mật khẩu - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="fw-bold text-center mb-3">Quên mật khẩu</h3>
                    <p class="text-muted">Nhập email tài khoản đặt lại mật khẩu.</p>

                    @if (session('status'))
                        <div class="alert alert-info">{{ session('status') }}</div>
                    @endif

                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Địa chỉ email</label>
                            <input id="email" type="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" required autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Gửi yêu cầu đặt lại mật khẩu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
