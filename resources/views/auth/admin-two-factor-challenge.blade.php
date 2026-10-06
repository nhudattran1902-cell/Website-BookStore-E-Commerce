@extends('layouts.app')

@section('title', 'Xác minh quản trị viên')

@section('content')
    <div class="container py-5" style="max-width: 480px">
        <div class="card border shadow-sm">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 fw-bold mb-2">Xác minh đăng nhập</h1>
                <p class="text-muted mb-4">
                    Mã OTP gồm 6 chữ số đã được gửi đến email quản trị viên <strong>{{ $email }}</strong>.
                    Mã có hiệu lực trong 5 phút và chỉ sử dụng được một lần.
                </p>

                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif

                <form action="{{ route('admin.2fa.verify') }}" method="POST">
                    @csrf
                    <label for="code" class="form-label fw-semibold">Mã 6 chữ số</label>
                    <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" class="form-control mb-2" required autofocus>
                    @error('code')<div class="text-danger small mb-3">{{ $message }}</div>@enderror
                    <button class="btn btn-primary w-100" type="submit">Xác minh</button>
                </form>

                <form action="{{ route('admin.2fa.resend') }}" method="POST" class="mt-2">
                    @csrf
                    <button class="btn btn-outline-secondary w-100" type="submit">Gửi lại mã OTP</button>
                </form>

                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button class="btn btn-link w-100" type="submit">Đăng xuất</button>
                </form>
            </div>
        </div>
    </div>
@endsection
