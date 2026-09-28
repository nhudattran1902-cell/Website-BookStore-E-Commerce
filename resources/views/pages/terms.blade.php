@extends('layouts.app')
@section('title', 'Điều khoản sử dụng - BOOK & BOX')
@section('content')
<div class="container py-5" style="max-width: 800px;">
    <h1 class="fw-bold mb-4">Điều khoản sử dụng</h1>
    <p class="text-muted">Cập nhật lần cuối: {{ now()->format('d/m/Y') }}</p>

    <div class="mt-4">
        <h5 class="fw-bold">1. Chấp nhận điều khoản</h5>
        <p>Khi truy cập và đặt mua sách trên BOOK & BOX, bạn đồng ý tuân thủ và chịu ràng buộc bởi các điều khoản sử dụng được quy định dưới đây.</p>

        <h5 class="fw-bold mt-4">2. Tài khoản và mật khẩu</h5>
        <p>Người dùng có trách nhiệm bảo mật thông tin tài khoản và mật khẩu của mình. Bạn phải thông báo ngay cho BOOK & BOX khi phát hiện hành vi truy cập trái phép vào tài khoản của mình.</p>

        <h5 class="fw-bold mt-4">3. Giá cả và thanh toán</h5>
        <p>Tất cả giá sản phẩm được niêm yết bằng Việt Nam Đồng (VNĐ). Chúng tôi hỗ trợ các phương thức thanh toán an toàn bao gồm COD (thanh toán khi nhận hàng), MoMo và VNPay.</p>

        <h5 class="fw-bold mt-4">4. Quyền sở hữu trí tuệ</h5>
        <p>Toàn bộ nội dung, hình ảnh bìa sách, biểu trưng, văn bản hiển thị trên website thuộc quyền sở hữu của BOOK & BOX hoặc các nhà xuất bản, tác giả hợp tác và được bảo vệ bởi luật pháp Việt Nam.</p>

        <h5 class="fw-bold mt-4">5. Thay đổi điều khoản</h5>
        <p>BOOK & BOX có quyền thay đổi, điều chỉnh điều khoản sử dụng bất kỳ lúc nào. Các thay đổi sẽ có hiệu lực ngay sau khi được đăng tải trên website.</p>
    </div>
</div>
@endsection

