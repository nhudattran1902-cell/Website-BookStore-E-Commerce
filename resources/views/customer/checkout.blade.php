@extends('layouts.app')

@section('title', 'Thanh toán đơn hàng - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row g-4">
            {{-- Thông tin giao hàng --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h4 class="fw-bold mb-4 text-dark">
                        <i class="bi bi-geo-alt-fill text-danger me-2"></i> Thông Tin Giao Hàng
                    </h4>
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" role="alert">
                            <div class="fw-bold mb-1">Không thể tiếp tục thanh toán:</div>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ và tên người nhận <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="ten_nguoi_nhan" class="form-control rounded-3"
                                value="{{ old('ten_nguoi_nhan', Auth::user()->ho_ten) }}" required
                                placeholder="Nhập họ tên đầy đủ">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="sdt_nguoi_nhan" class="form-control rounded-3"
                                    value="{{ old('sdt_nguoi_nhan', Auth::user()->so_dien_thoai) }}" required
                                    placeholder="Nhập số điện thoại">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email xác nhận</label>
                                <input type="email" class="form-control rounded-3 bg-light"
                                    value="{{ Auth::user()->email }}" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Địa chỉ nhận hàng <span class="text-danger">*</span></label>
                            <textarea name="dia_chi_giao_hang" class="form-control rounded-3" rows="3" required
                                placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố">{{ old('dia_chi_giao_hang') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Ghi chú đơn hàng (Tùy chọn)</label>
                            <textarea name="ghi_chu" class="form-control rounded-3" rows="2"
                                placeholder="Ghi chú thêm về thời gian giao hoặc hướng dẫn giao hàng">{{ old('ghi_chu') }}</textarea>
                        </div>

                        <h5 class="fw-bold mb-3 text-dark">
                            <i class="bi bi-credit-card-2-front-fill text-primary me-2"></i> Phương Thức Thanh Toán
                        </h5>

                        <div class="d-flex flex-column gap-2 mb-4">
                            <label
                                class="border rounded-3 p-3 d-flex align-items-center cursor-pointer hover-border-danger">
                                <input type="radio" name="phuong_thuc_thanh_toan" value="COD"
                                    class="form-check-input me-3" @checked(old('phuong_thuc_thanh_toan', 'COD') === 'COD')>
                                <div class="flex-grow-1">
                                    <div class="fw-bold"><i class="bi bi-cash-stack text-success me-1"></i> Thanh toán khi
                                        nhận hàng (COD)</div>
                                    <small class="text-muted">Thanh toán bằng tiền mặt trực tiếp cho shipper khi nhận
                                        sách</small>
                                </div>
                            </label>

                            <label
                                class="border rounded-3 p-3 d-flex align-items-center cursor-pointer hover-border-danger">
                                <input type="radio" name="phuong_thuc_thanh_toan" value="MoMo"
                                    class="form-check-input me-3" @checked(old('phuong_thuc_thanh_toan') === 'MoMo')>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-danger"><i class="bi bi-wallet2 me-1"></i> Ví điện tử MoMo
                                    </div>
                                    <small class="text-muted">Thanh toán an toàn qua ứng dụng Ví MoMo</small>
                                </div>
                            </label>

                            <label
                                class="border rounded-3 p-3 d-flex align-items-center cursor-pointer hover-border-danger">
                                <input type="radio" name="phuong_thuc_thanh_toan" value="VNPay"
                                    class="form-check-input me-3" @checked(old('phuong_thuc_thanh_toan') === 'VNPay')>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-primary"><i class="bi bi-qr-code-scan me-1"></i> Cổng thanh
                                        toán VNPay</div>
                                    <small class="text-muted">Thanh toán qua quét mã QR Ngân hàng / Internet Banking</small>
                                </div>
                            </label>

                            <label class="border rounded-3 p-3 d-flex align-items-center cursor-pointer hover-border-danger">
                                <input type="radio" name="phuong_thuc_thanh_toan" value="BankTransfer"
                                    class="form-check-input me-3" @checked(old('phuong_thuc_thanh_toan') === 'BankTransfer')>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-success"><i class="bi bi-bank me-1"></i> Chuyển khoản VietQR</div>
                                    <small class="text-muted">Đơn được xác nhận sau khi đối soát giao dịch ngân hàng</small>
                                </div>
                            </label>
                        </div>

                        <p class="small text-muted mb-4">Đơn thanh toán trực tuyến sẽ được giữ hàng tối đa 15 phút. Bạn sẽ chuyển tới cổng tương ứng sau khi đặt hàng.</p>

                        <button type="submit" class="btn btn-danger btn-lg w-100 rounded-pill fw-bold shadow-sm py-3">
                            <i class="bi bi-receipt me-2"></i> Đặt hàng & tiếp tục thanh toán
                        </button>
                    </form>
                </div>
            </div>

            {{-- Tóm tắt đơn hàng --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Đơn Hàng Của Bạn</h5>

                    <div class="cart-items-summary overflow-auto mb-3" style="max-height: 320px;">
                        @forelse($cartItems as $item)
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                <img src="{{ $item->sach->anh_bia_url ?: asset('images/no-cover.jpg') }}"
                                    class="rounded-2 me-3" style="width: 50px; height: 68px; object-fit: cover;">
                                <div class="flex-grow-1 pe-2">
                                    <h6 class="fw-bold mb-1 text-truncate-2 small">{{ $item->sach->tieu_de }}</h6>
                                    <small class="text-muted">Số lượng: {{ $item->so_luong }}</small>
                                </div>
                                <span class="fw-bold text-danger small">
                                    {{ number_format(($item->sach->gia_khuyen_mai ?? $item->sach->gia_ban) * $item->so_luong, 0, ',', '.') }}
                                    đ
                                </span>
                            </div>
                        @empty
                            <p class="text-muted">Không có sản phẩm nào trong giỏ.</p>
                        @endforelse
                    </div>

                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tạm tính:</span>
                            <span class="fw-bold">{{ number_format($total, 0, ',', '.') }} đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Phí vận chuyển:</span>
                            <span class="text-success fw-bold">Miễn phí</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-0">
                            <span class="fw-bold fs-5">Tổng thanh toán:</span>
                            <span class="fw-bold fs-4 text-danger">{{ number_format($total, 0, ',', '.') }} đ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
