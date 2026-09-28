@extends('layouts.app')

@section('title', 'Thanh toán đơn hàng - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <h2 class="fw-bold mb-4">Thanh toán đơn hàng</h2>

        @if (session('error'))
            <div class="alert alert-danger mb-4">{{ session('error') }}</div>
        @endif

        <form action="{{ route('checkout.process') }}" method="POST">
            @csrf
            <div class="row g-4">
                <!-- Thông tin giao hàng -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3">Thông tin người nhận</h5>

                        <div class="mb-3">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="ho_ten" class="form-control" required
                                placeholder="Nhập họ tên người nhận">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="so_dien_thoai" class="form-control" required
                                placeholder="Nhập số điện thoại">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                            <textarea name="dia_chi_giao_hang" class="form-control" rows="3" required
                                placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ghi chú đơn hàng</label>
                            <textarea name="ghi_chu" class="form-control" rows="2" placeholder="Lưu ý cho người giao hàng..."></textarea>
                        </div>

                        <h5 class="fw-bold mt-4 mb-3">Phương thức thanh toán</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="phuong_thuc_thanh_toan" id="paymentCOD"
                                value="COD" checked>
                            <label class="form-check-label" for="paymentCOD">
                                Thanh toán khi nhận hàng (COD)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="phuong_thuc_thanh_toan" id="paymentMoMo"
                                value="MoMo">
                            <label class="form-check-label" for="paymentMoMo">
                                Ví điện tử MoMo
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="phuong_thuc_thanh_toan" id="paymentVNPay"
                                value="VNPay">
                            <label class="form-check-label" for="paymentVNPay">
                                Cổng thanh toán VNPay
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Tóm tắt đơn hàng -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3">Sách mua</h5>
                        <ul class="list-group list-group-flush mb-3">
                            @foreach ($cartItems as $item)
                                @php
                                    $gia = $item->sach->gia_khuyen_mai ?? $item->sach->gia_ban;
                                @endphp
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <h6 class="my-0">{{ $item->sach->ten_sach }}</h6>
                                        <small class="text-muted">x {{ $item->so_luong }}</small>
                                    </div>
                                    <span class="fw-bold">{{ number_format($gia * $item->so_luong, 0, ',', '.') }} đ</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="d-flex justify-content-between fs-5 mb-4">
                            <span class="fw-bold">Tổng thanh toán:</span>
                            <span class="fw-bold text-danger">{{ number_format($tongTien, 0, ',', '.') }} đ</span>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 py-3 fw-bold text-uppercase">
                            Xác nhận đặt hàng
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
