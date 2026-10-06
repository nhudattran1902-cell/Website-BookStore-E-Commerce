@extends('layouts.app')
@section('title', 'Liên hệ - BOOK & BOX')
@section('content')
<div class="container py-5" style="max-width: 800px;">
    <h1 class="fw-bold mb-4">Liên hệ với chúng tôi</h1>

    <div class="row g-5">
        <div class="col-md-5">
            <h5 class="fw-bold mb-3">Thông tin liên hệ</h5>
            <ul class="list-unstyled">
                <li class="mb-3">
                    <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                    <strong>Địa chỉ:</strong><br>
                    <span class="text-muted ms-4">12 Trịnh Đình Thảo, Phường Tân Phú,<br>TP. Hồ Chí Minh</span>
                </li>
                <li class="mb-3">
                    <i class="bi bi-telephone-fill text-success me-2"></i>
                    <strong>Điện thoại:</strong><br>
                    <span class="text-muted ms-4">+84 767 417 206</span>
                </li>
                <li class="mb-3">
                    <i class="bi bi-envelope-fill text-primary me-2"></i>
                    <strong>Email:</strong><br>
                    <span class="text-muted ms-4">nhudattran1902@gmail.com</span>
                </li>
                <li>
                    <i class="bi bi-clock-fill text-warning me-2"></i>
                    <strong>Giờ làm việc:</strong><br>
                    <span class="text-muted ms-4">Thứ 2 – Thứ 7: 8:00 – 21:00<br>Chủ nhật: 9:00 – 18:00</span>
                </li>
            </ul>
        </div>
        <div class="col-md-7">
            <h5 class="fw-bold mb-3">Gửi tin nhắn cho chúng tôi</h5>
            @if (session('contact_success'))
                <div class="alert alert-success" role="status">{{ session('contact_success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('pages.contact.send') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="contact-name">Họ và tên</label>
                    <input id="contact-name" type="text" name="ten_lien_he" class="form-control"
                        value="{{ old('ten_lien_he', Auth::user()?->ho_ten ?? '') }}" required maxlength="150"
                        placeholder="Nguyễn Văn A">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="contact-email">Email</label>
                    <input id="contact-email" type="email" name="email_lien_he" class="form-control"
                        value="{{ old('email_lien_he', Auth::user()?->email ?? '') }}" required maxlength="255"
                        placeholder="email@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="contact-message">Nội dung</label>
                    <textarea id="contact-message" name="noi_dung" class="form-control" rows="5" maxlength="2000"
                        required placeholder="Nhập nội dung tin nhắn...">{{ old('noi_dung') }}</textarea>
                </div>
                <button type="submit" class="btn btn-dark px-4"><i class="bi bi-send me-1"></i>Gửi tin nhắn</button>
            </form>
        </div>
    </div>
</div>
@endsection

