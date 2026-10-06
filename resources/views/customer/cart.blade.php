@extends('layouts.app')

@section('title', 'Giỏ hàng của bạn - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <h2 class="fw-bold mb-4">Giỏ hàng của bạn</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (isset($cartItems) && $cartItems->count() > 0)
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-0">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Sản phẩm</th>
                                        <th>Giá</th>
                                        <th style="width: 120px;">Số lượng</th>
                                        <th>Tổng</th>
                                        <th class="text-end pe-3">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalPrice = 0; @endphp
                                    @foreach ($cartItems as $item)
                                        @php
                                            $thanhTien = $item->sach->gia_ban * $item->so_luong;
                                            $totalPrice += $thanhTien;
                                        @endphp
                                        <tr>
                                            <td class="ps-3 d-flex align-items-center gap-3">
                                                <img src="{{ $item->sach->anh_bia_url ?: asset('images/no-cover.jpg') }}"
                                                    alt="{{ $item->sach->tieu_de }}"
                                                    style="width: 50px; height: 70px; object-fit: cover;"
                                                    class="rounded shadow-sm">
                                                <div>
                                                    <h6 class="fw-bold mb-0">{{ $item->sach->tieu_de }}</h6>
                                                </div>
                                            </td>
                                            <td class="fw-bold text-danger">
                                                {{ number_format($item->sach->gia_ban, 0, ',', '.') }} đ</td>
                                            <td>
                                                <form action="{{ url('/cart/update/' . $item->id) }}" method="POST"
                                                    class="d-flex">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="number" name="so_luong" value="{{ $item->so_luong }}"
                                                        min="1" class="form-control form-control-sm text-center me-1"
                                                        onchange="this.form.submit()">
                                                </form>
                                            </td>
                                            <td class="fw-bold text-danger">{{ number_format($thanhTien, 0, ',', '.') }} đ
                                            </td>
                                            <td class="text-end pe-3">
                                                <form action="{{ url('/cart/remove/' . $item->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i
                                                            class="bi bi-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">Tổng cộng</h5>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tạm tính:</span>
                                <span class="fw-bold">{{ number_format($totalPrice, 0, ',', '.') }} đ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-4">
                                <span>Phí vận chuyển:</span>
                                <span>Miễn phí</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold fs-5">Thành tiền:</span>
                                <span class="fw-bold fs-5 text-danger">{{ number_format($totalPrice, 0, ',', '.') }}
                                    đ</span>
                            </div>
                            <a href="{{ url('/checkout') }}" class="btn btn-dark w-100 py-2 fw-bold">Tiến hành thanh
                                toán</a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <h4 class="text-muted mb-3">Giỏ hàng của bạn đang trống</h4>
                <a href="{{ url('/books') }}" class="btn btn-primary">Tiếp tục mua sắm</a>
            </div>
        @endif
    </div>
@endsection
