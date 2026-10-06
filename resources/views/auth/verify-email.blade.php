@extends('layouts.app')

@section('title', 'Xác minh email - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card border-0 shadow-sm p-4 text-center">
                    <h3 class="fw-bold mb-3">Xác minh email</h3>
                    <p>Hãy mở email và nhấn liên kết xác minh trước khi thanh toán hoặc gửi đánh giá.</p>

                    @if (session('success') || session('status'))
                        <div class="alert alert-success">
                            {{ session('success') ?? session('status') }}
                        </div>
                    @endif

                    <form action="{{ route('verification.send') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-dark">Gửi lại liên kết xác minh</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
