@extends('layouts.app')
@section('title', 'Chính sách bảo mật - BOOK & BOX')
@section('content')
<div class="container py-5" style="max-width: 800px;">
    <h1 class="fw-bold mb-4">Chính sách bảo mật</h1>
    <p class="text-muted">Cập nhật lần cuối: {{ now()->format('d/m/Y') }}</p>

    <div class="mt-4">
        <h5 class="fw-bold">1. Thu thập thông tin cá nhân</h5>
        <p>BOOK & BOX chỉ thu thập các thông tin cần thiết phục vụ cho quá trình mua hàng và giao nhận sách, bao gồm: Họ và tên, số điện thoại, địa chỉ email, địa chỉ nhận hàng.</p>

        <h5 class="fw-bold mt-4">2. Mục đích sử dụng thông tin</h5>
        <ul>
            <li>Xử lý và hoàn tất đơn hàng của bạn.</li>
            <li>Thông báo trạng thái đơn hàng và lịch trình giao hàng.</li>
            <li>Hỗ trợ khách hàng và giải quyết các khiếu nại liên quan đến đơn hàng.</li>
            <li>Cải thiện trải nghiệm mua sắm trên website.</li>
        </ul>

        <h5 class="fw-bold mt-4">3. Bảo mật thông tin</h5>
        <p>Chúng tôi cam kết bảo mật tuyệt đối thông tin cá nhân của khách hàng theo chính sách bảo vệ thông tin cá nhân. Chúng tôi áp dụng các biện pháp an toàn kỹ thuật nhằm ngăn chặn việc truy cập trái phép.</p>

        <h5 class="fw-bold mt-4">4. Chia sẻ thông tin</h5>
        <p>BOOK & BOX không bán, chia sẻ hay trao đổi thông tin cá nhân của khách hàng cho bên thứ ba nào khác ngoại trừ các đơn vị vận chuyển đối tác để phục vụ việc giao hàng.</p>

        <h5 class="fw-bold mt-4">5. Quyền của khách hàng</h5>
        <p>Bạn có toàn quyền kiểm tra, cập nhật hoặc xóa thông tin cá nhân của mình bằng cách đăng nhập vào tài khoản và truy cập phần <a href="{{ route('customer.profile') }}" class="text-dark fw-bold">Hồ sơ cá nhân</a>.</p>
    </div>
</div>
@endsection

