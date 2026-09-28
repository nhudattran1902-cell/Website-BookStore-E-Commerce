@extends('layouts.app')

@section('title', 'Đặt hàng thành công - BOOK & BOX')

@section('content')
    <div class="container py-5 text-center">
        <div class="card border-0 shadow-sm p-5 mx-auto" style="max-width: 600px;">
            <i class="bi bi-check-circle-fill text-success display-1 mb-3"></i>
            <h2 class="fw-bold mb-2">Đặt hàng thành công!</h2>
            <p class="text-muted">Cảm ơn bạn đã mua sách tại BOOK & BOX.</p>

            <div class="alert alert-light border my-4 text-start">
                <p class="mb-1"><strong>Mã đơn hàng:</strong> {{ $donHang->ma_don_hang }}</p>
                <p class="mb-1"><strong>Tổng tiền:</strong> {{ number_format($donHang->thanh_tien, 0, ',', '.') }} đ</p>
                <p class="mb-0"><strong>Trạng thái:</strong> <span class="badge bg-warning text-dark">Chờ xử lý</span></p>
            </div>

            <div class="d-flex gap-2 justify-content-center">
                <a href="{{ url('/books') }}" class="btn btn-primary px-4">Tiếp tục mua sách</a>
            </div>
        </div>
    </div>
@endsection
