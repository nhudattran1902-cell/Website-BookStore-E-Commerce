@extends('layouts.app')
@section('title', 'Chính sách vận chuyển - BOOK & BOX')
@section('content')
<div class="container py-5" style="max-width: 800px;">
    <h1 class="fw-bold mb-4">Chính sách vận chuyển</h1>
    <p class="text-muted">Cập nhật lần cuối: {{ now()->format('d/m/Y') }}</p>

    <div class="mt-4">
        <h5 class="fw-bold">1. Phạm vi giao hàng</h5>
        <p>BOOK & BOX giao hàng toàn quốc tới tất cả 63 tỉnh thành tại Việt Nam.</p>

        <h5 class="fw-bold mt-4">2. Thời gian giao hàng</h5>
        <ul>
            <li><strong>Nội thành TP.HCM:</strong> 1–2 ngày làm việc</li>
            <li><strong>Các tỉnh miền Nam:</strong> 2–3 ngày làm việc</li>
            <li><strong>Miền Trung và miền Bắc:</strong> 3–5 ngày làm việc</li>
            <li><strong>Vùng sâu, vùng xa:</strong> 5–7 ngày làm việc</li>
        </ul>

        <h5 class="fw-bold mt-4">3. Phí vận chuyển</h5>
        <p>Miễn phí vận chuyển cho tất cả đơn hàng. Chúng tôi chịu hoàn toàn chi phí giao hàng đến tay khách hàng.</p>

        <h5 class="fw-bold mt-4">4. Đóng gói</h5>
        <p>Tất cả sách được đóng gói cẩn thận bằng túi nilon và bìa carton để bảo vệ sách trong quá trình vận chuyển.</p>

        <h5 class="fw-bold mt-4">5. Theo dõi đơn hàng</h5>
        <p>Sau khi đặt hàng thành công, bạn có thể theo dõi trạng thái đơn hàng trong mục <a href="{{ route('customer.orders.index') }}" class="text-dark fw-bold">Lịch sử đơn hàng</a>.</p>
    </div>
</div>
@endsection

