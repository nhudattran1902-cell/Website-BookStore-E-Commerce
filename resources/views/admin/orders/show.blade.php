@extends('admin.layouts.master')

@section('title', 'Chi tiết đơn hàng ' . $donHang->ma_don_hang)

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Chi tiết đơn hàng: {{ $donHang->ma_don_hang }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Quay
            lại</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card p-3 shadow-sm border-0 mb-4">
                <h5 class="fw-bold mb-3">Sách đã đặt</h5>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Sách</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($donHang->chiTietDonHang as $ct)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $ct->sach && $ct->sach->anh_bia ? asset('storage/' . $ct->sach->anh_bia) : asset('images/no-cover.jpg') }}"
                                                width="40" class="rounded">
                                            <span>{{ $ct->sach->tieu_de ?? 'Sách đã bị xóa' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ number_format($ct->don_gia, 0, ',', '.') }} đ</td>
                                    <td>{{ $ct->so_luong }}</td>
                                    <td class="fw-bold">{{ number_format($ct->thanh_tien, 0, ',', '.') }} đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0 mb-4">
                <h5 class="fw-bold mb-3">Thông tin nhận hàng</h5>
                <p><strong>Người nhận:</strong> {{ $donHang->nguoiDung->ho_ten ?? 'N/A' }}</p>
                <p><strong>Địa chỉ:</strong> {{ $donHang->dia_chi_giao_hang }}</p>
                <p><strong>Ghi chú:</strong> {{ $donHang->ghi_chu ?? 'Không có' }}</p>
                <hr>
                <p><strong>Tổng tiền:</strong> {{ number_format($donHang->tong_tien, 0, ',', '.') }} đ</p>
                <p><strong>Thanh toán:</strong> <span
                        class="fw-bold text-success">{{ number_format($donHang->thanh_tien, 0, ',', '.') }} đ</span></p>
            </div>

            <div class="card p-3 shadow-sm border-0">
                <h5 class="fw-bold mb-3">Cập nhật trạng thái</h5>
                <form action="{{ route('admin.orders.updateStatus', $donHang->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <select name="trang_thai" class="form-select">
                            <option value="cho_xu_ly" {{ $donHang->trang_thai == 'cho_xu_ly' ? 'selected' : '' }}>Chờ xử lý</option>
                            <option value="dang_xu_ly" {{ $donHang->trang_thai == 'dang_xu_ly' ? 'selected' : '' }}>Đang xử lý</option>
                            <option value="dang_giao" {{ $donHang->trang_thai == 'dang_giao' ? 'selected' : '' }}>Đang giao</option>
                            <option value="hoan_thanh" {{ $donHang->trang_thai == 'hoan_thanh' ? 'selected' : '' }}>Hoàn thành</option>
                            <option value="da_huy" {{ $donHang->trang_thai == 'da_huy' ? 'selected' : '' }}>Đã hủy</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Cập nhật</button>
                </form>
            </div>
        </div>
    </div>
@endsection
