@extends('layouts.app')
@section('title', 'Về chúng tôi - BOOK & BOX')
@section('content')
<div class="container py-5" style="max-width: 800px;">
    <h1 class="fw-bold mb-4">Về chúng tôi</h1>
    <p class="lead text-muted mb-4">BOOK & BOX là cửa hàng sách trực tuyến chuyên cung cấp các đầu sách chất lượng cao tại Việt Nam.</p>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="text-center p-4 border rounded-3">
                <i class="bi bi-book" style="font-size: 2.5rem; color: #212529;"></i>
                <h5 class="fw-bold mt-3">10,000+ đầu sách</h5>
                <p class="text-muted small">Đa dạng thể loại, từ văn học đến khoa học kỹ thuật</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-4 border rounded-3">
                <i class="bi bi-truck" style="font-size: 2.5rem; color: #212529;"></i>
                <h5 class="fw-bold mt-3">Giao hàng toàn quốc</h5>
                <p class="text-muted small">Đến tận nhà, nhanh chóng và an toàn</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-4 border rounded-3">
                <i class="bi bi-shield-check" style="font-size: 2.5rem; color: #212529;"></i>
                <h5 class="fw-bold mt-3">Sách chính hãng 100%</h5>
                <p class="text-muted small">Cam kết sách thật, chất lượng đảm bảo</p>
            </div>
        </div>
    </div>

    <h4 class="fw-bold mb-3">Câu chuyện của chúng tôi</h4>
    <p>BOOK & BOX được thành lập với sứ mệnh mang văn hóa đọc sách đến gần hơn với mọi người. Chúng tôi tin rằng mỗi cuốn sách là một cánh cửa mở ra thế giới mới.</p>
    <p>Với đội ngũ yêu sách và am hiểu thị trường sách Việt Nam, chúng tôi lựa chọn kỹ lưỡng từng đầu sách để mang đến trải nghiệm đọc sách tốt nhất cho khách hàng.</p>

    <div class="mt-4 p-4 bg-light rounded-3">
        <h5 class="fw-bold">Thông tin liên hệ</h5>
        <p class="mb-1"><i class="bi bi-geo-alt me-2"></i> 12 Trịnh Đình Thảo, Phường Tân Phú, TP. Hồ Chí Minh</p>
        <p class="mb-1"><i class="bi bi-telephone me-2"></i> +84 767 417 206</p>
        <p class="mb-0"><i class="bi bi-envelope me-2"></i> nhudattran1902@gmail.com</p>
    </div>
</div>
@endsection

