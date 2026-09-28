@extends('layouts.app')
@section('title', 'Câu hỏi thường gặp (FAQ) - BOOK & BOX')
@section('content')
<div class="container py-5" style="max-width: 800px;">
    <h1 class="fw-bold mb-4">Câu hỏi thường gặp (FAQ)</h1>
    <p class="text-muted mb-4">Giải đáp các thắc mắc phổ biến của khách hàng khi mua sắm tại BOOK & BOX.</p>

    <div class="accordion" id="faqAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Làm thế nào để tôi đặt mua sách tại BOOK & BOX?
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Bạn chỉ cần tìm kiếm hoặc chọn cuốn sách ưng ý, bấm <strong>Thêm vào giỏ hàng</strong>, kiểm tra giỏ hàng và tiến hành thanh toán bằng cách điền thông tin người nhận và chọn phương thức thanh toán.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Thời gian giao hàng mất bao lâu?
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Thời gian giao hàng thông thường từ 1-2 ngày đối với nội thành TP.HCM, và 2-5 ngày đối với các tỉnh thành khác. Bạn có thể xem chi tiết tại <a href="{{ route('pages.shipping') }}">Chính sách vận chuyển</a>.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Tôi có thể đọc thử sách trước khi mua không?
                </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Có! Đối với những cuốn sách có hỗ trợ đọc thử, bạn có thể bấm nút <strong>Đọc ngay</strong> tại trang chi tiết sách để xem các trang mẫu miễn phí.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                    Tôi có được kiểm tra hàng trước khi nhận không?
                </button>
            </h2>
            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Bạn được phép đồng kiểm bên ngoài kiện hàng cùng nhân viên giao vận. Nếu sách có dấu hiệu hư hỏng do vận chuyển hoặc giao sai sách, bạn có thể từ chối nhận hàng hoặc yêu cầu đổi trả theo <a href="{{ route('pages.return-policy') }}">Chính sách đổi trả</a>.
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFive">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                    Tôi cần hỗ trợ thêm thì liên hệ bằng cách nào?
                </button>
            </h2>
            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Bạn có thể liên hệ trực tiếp qua hotline <strong>+84 767 417 206</strong>, gửi thư đến <strong>nhudattran1902@gmail.com</strong> hoặc để lại lời nhắn tại trang <a href="{{ route('pages.contact') }}">Liên hệ</a>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

