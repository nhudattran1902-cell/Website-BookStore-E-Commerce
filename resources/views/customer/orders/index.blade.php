@extends('layouts.app')

@section('title', 'Lịch sử đơn hàng - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <h2 class="fw-bold mb-4">Lịch sử đơn hàng của tôi</h2>

        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($danhSachDonHang as $dh)
                            <tr>
                                <td class="fw-bold">{{ $dh->ma_don_hang }}</td>
                                <td>{{ $dh->ngay_tao ? \Carbon\Carbon::parse($dh->ngay_tao)->format('d/m/Y H:i') : '' }}</td>
                                <td class="fw-bold text-success">{{ number_format($dh->thanh_tien, 0, ',', '.') }} đ</td>
                                <td>
                                    @switch($dh->trang_thai)
                                        @case('cho_xu_ly')
                                            <span class="badge bg-warning text-dark">Chờ xử lý</span>
                                        @break

                                        @case('dang_xu_ly')
                                            <span class="badge bg-info text-dark">Đang xử lý</span>
                                        @break

                                        @case('dang_giao')
                                            <span class="badge bg-primary">Đang giao</span>
                                        @break

                                        @case('hoan_thanh')
                                            <span class="badge bg-success">Hoàn thành</span>
                                        @break

                                        @case('da_huy')
                                            <span class="badge bg-danger">Đã hủy</span>
                                        @break
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('customer.orders.show', $dh->id) }}"
                                        class="btn btn-sm btn-outline-dark">
                                        Xem chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Bạn chưa có đơn hàng nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $danhSachDonHang->links() }}
            </div>
        </div>
    </div>
@endsection
