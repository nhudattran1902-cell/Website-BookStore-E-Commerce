@extends('layouts.app')

@section('title', 'Thanh toán demo đơn hàng '.$order->ma_don_hang)

@section('content')
    <div class="container py-5" style="max-width: 760px">
        <div class="alert alert-warning" role="alert">
            <strong>Chế độ DEMO:</strong> thao tác bên dưới không chuyển tiền thật và chỉ dùng để thử luồng thanh toán.
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <p class="text-uppercase text-muted small fw-bold mb-2">{{ $paymentMethod === 'MoMo' ? 'MoMo' : 'VietQR' }}</p>
                <h1 class="h3 fw-bold mb-2">Thanh toán đơn #{{ $order->ma_don_hang }}</h1>
                <p class="text-muted">Mô phỏng giao dịch để kiểm tra thông báo cho khách hàng và quản trị viên.</p>

                <div class="d-flex justify-content-between border-top border-bottom py-3 my-4">
                    <span>Số tiền</span>
                    <strong>{{ number_format($order->thanh_tien, 0, ',', '.') }} đ</strong>
                </div>

                <form method="POST" action="{{ route('checkout.payment.demo', $order->ma_don_hang) }}">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-check-circle me-2" aria-hidden="true"></i>Mô phỏng thanh toán thành công
                    </button>
                </form>

                <a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-link w-100 mt-2">Quay lại đơn hàng</a>
            </div>
        </div>
    </div>
@endsection