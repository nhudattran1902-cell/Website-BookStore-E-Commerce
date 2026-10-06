@extends('layouts.app')

@section('title', 'Chuyển khoản đơn hàng '.$order->ma_don_hang)

@section('content')
    <div class="container py-5" style="max-width: 900px">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Thanh toán đơn #{{ $order->ma_don_hang }}</h1>
                <p class="text-muted mb-0">Đơn được xác nhận sau khi giao dịch được đối soát.</p>
            </div>
            <span class="badge bg-warning-subtle text-warning-emphasis">Đang chờ thanh toán</span>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-md-5 text-center">
                <img src="{{ $qrUrl }}" alt="VietQR thanh toán đơn {{ $order->ma_don_hang }}" class="img-fluid border rounded bg-white p-2" width="320" height="320">
            </div>
            <div class="col-md-7">
                <dl class="row mb-4">
                    <dt class="col-sm-4">Ngân hàng</dt><dd class="col-sm-8">{{ $bankId }}</dd>
                    <dt class="col-sm-4">Số tài khoản</dt><dd class="col-sm-8 font-monospace">{{ $accountNumber }}</dd>
                    <dt class="col-sm-4">Tên tài khoản</dt><dd class="col-sm-8">{{ $accountName }}</dd>
                    <dt class="col-sm-4">Số tiền</dt><dd class="col-sm-8 fw-bold text-danger">{{ number_format($order->thanh_tien, 0, ',', '.') }} đ</dd>
                    <dt class="col-sm-4">Nội dung</dt><dd class="col-sm-8 font-monospace text-break">{{ $order->ma_don_hang }} SIG:{{ $signature }}</dd>
                </dl>
                <p class="small text-muted">Không chỉnh sửa số tiền hoặc nội dung chuyển khoản. Sau khi chuyển, trạng thái sẽ giữ ở chờ thanh toán đến khi admin kiểm tra giao dịch trên tài khoản ngân hàng.</p>
                <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-outline-primary">Xem trạng thái đơn</a>
            </div>
        </div>
    </div>
@endsection