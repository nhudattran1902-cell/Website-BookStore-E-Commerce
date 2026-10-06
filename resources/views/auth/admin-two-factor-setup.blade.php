@extends('layouts.app')

@section('title', 'Thiết lập xác thực hai bước')

@section('content')
    <div class="container py-5" style="max-width: 620px">
        <div class="card border shadow-sm">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 fw-bold mb-2">Xác minh email quản trị</h1>
                <p class="text-muted mb-4">
                    Mã OTP sẽ được gửi đến email quản trị viên sau khi bạn đăng nhập.
                </p>
                <a class="btn btn-primary w-100" href="{{ route('admin.2fa.challenge') }}">Tiếp tục xác minh</a>
            </div>
        </div>
    </div>
@endsection
