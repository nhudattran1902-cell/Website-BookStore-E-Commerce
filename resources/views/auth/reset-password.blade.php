@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="fw-bold text-center mb-4">Đặt lại mật khẩu</h3>

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Địa chỉ email</label>
                            <input id="email" type="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $email) }}" required autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Mật khẩu mới</label>
                            <input id="password" type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror" required
                                autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-bold">Nhập lại mật khẩu</label>
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                class="form-control" required autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Lưu mật khẩu mới</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
