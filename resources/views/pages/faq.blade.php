@extends('layouts.app')

@section('title', 'Câu Hỏi Thường Gặp (FAQ) - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <span class="badge bg-danger rounded-pill px-3 py-2 mb-2">Hỗ Trợ Khách Hàng</span>
                    <h2 class="fw-bold">Câu Hỏi Thường Gặp</h2>
                    <p class="text-muted">Giải đáp các thắc mắc về đơn hàng, vận chuyển và đổi trả tại BOOK & BOX</p>
                </div>

                {{-- Container Accordion FAQ --}}
                <div class="accordion shadow-sm rounded-4 overflow-hidden" id="faqAccordion">

                    {{-- Câu 1 --}}
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button fw-bold py-3" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                <i class="bi bi-question-circle text-danger me-2"></i> Làm thế nào để tôi đặt hàng trực
                                tuyến?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
                            data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Bạn chỉ cần tìm kiếm cuốn sách yêu thích, bấm <strong>"Thêm vào giỏ hàng"</strong> hoặc
                                <strong>"Mua ngay"</strong>, sau đó điền thông tin giao hàng và chọn phương thức thanh toán
                                phù hợp (COD, MoMo, VNPay) để hoàn tất đơn hàng.
                            </div>
                        </div>
                    </div>

                    {{-- Câu 2 --}}
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                <i class="bi bi-truck text-danger me-2"></i> Thời gian và chi phí giao hàng là bao nhiêu?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                            data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Thời gian giao hàng nội thành TPHCM và Hà Nội từ 1-2 ngày, các tỉnh thành khác từ 3-5 ngày
                                làm việc. Đơn hàng từ 250.000đ sẽ được miễn phí vận chuyển trên toàn quốc.
                            </div>
                        </div>
                    </div>

                    {{-- Câu 3 --}}
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                <i class="bi bi-arrow-counterclockwise text-danger me-2"></i> Chính sách đổi trả sách hư
                                hỏng như thế nào?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                            data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                BOOK & BOX hỗ trợ đổi trả miễn phí trong vòng 7 ngày nếu sách bị lỗi in ấn, rách bìa hoặc hư
                                hỏng trong quá trình vận chuyển. Quý khách vui lòng giữ lại hóa đơn và quay video khi mở
                                hàng.
                            </div>
                        </div>
                    </div>

                    {{-- Câu 4 --}}
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                <i class="bi bi-credit-card text-danger me-2"></i> Tôi có thể thanh toán bằng những hình
                                thức nào?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                            data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">
                                Hệ thống hỗ trợ thanh toán khi nhận hàng (COD), Ví điện tử MoMo và Cổng thanh toán quét mã
                                QR VNPay.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
