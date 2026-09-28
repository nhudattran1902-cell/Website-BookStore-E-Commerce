@extends('layouts.app')
@section('title', 'Chính sách đổi trả - BOOK & BOX')
@section('content')
<div class="container py-5" style="max-width: 800px;">
    <h1 class="fw-bold mb-4">Chính sách đổi trả</h1>
    <p class="text-muted">Cập nhật lần cuối: {{ now()->format('d/m/Y') }}</p>

    <div class="mt-4">
        <h5 class="fw-bold">1. Điều kiện đổi trả</h5>
        <p>Chúng tôi chấp nhận đổi/trả sách trong vòng <strong>7 ngày</strong> kể từ ngày nhận hàng nếu:</p>
        <ul>
            <li>Sách bị lỗi in ấn, rách trang, in mờ</li>
            <li>Sách giao không đúng tên/phiên bản đã đặt</li>
            <li>Sách bị hư hỏng trong quá trình vận chuyển</li>
        </ul>

        <h5 class="fw-bold mt-4">2. Không áp dụng đổi trả</h5>
        <ul>
            <li>Sách đã đọc qua (có dấu mở sách, gấp trang)</li>
            <li>Sách bị hư do lỗi của khách hàng</li>
            <li>Đổi trả sau 7 ngày kể từ ngày nhận hàng</li>
        </ul>

        <h5 class="fw-bold mt-4">3. Quy trình đổi trả</h5>
        <ol>
            <li>Liên hệ với chúng tôi qua email: <strong>nhudattran1902@gmail.com</strong></li>
            <li>Cung cấp mã đơn hàng và ảnh chụp sách bị lỗi</li>
            <li>Chúng tôi xác nhận và hướng dẫn gửi trả sách</li>
            <li>Sau khi nhận sách, chúng tôi gửi sách mới trong vòng 3 ngày làm việc</li>
        </ol>

        <h5 class="fw-bold mt-4">4. Hoàn tiền</h5>
        <p>Trường hợp sách không còn hàng, chúng tôi sẽ hoàn tiền 100% về phương thức thanh toán ban đầu trong vòng 5–7 ngày làm việc.</p>
    </div>
</div>
@endsection

