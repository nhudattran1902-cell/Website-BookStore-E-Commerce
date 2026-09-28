@extends('admin.layouts.master')

@section('title', 'Quản lý Đơn hàng - BOOK & BOX')

@section('content')
    <div class="page-header mb-4">
        <h1 class="page-title">Quản lý Đơn hàng</h1>
        <p class="page-subtitle">Danh sách tất cả đơn đặt hàng của khách hàng.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    <!-- Bộ lọc trạng thái đơn hàng -->
    <div class="mb-3">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="d-flex gap-2">
            <select name="trang_thai" class="form-select w-auto" onchange="this.form.submit()">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="cho_xu_ly" {{ request('trang_thai') == 'cho_xu_ly' ? 'selected' : '' }}>Chờ xử lý</option>
                <option value="dang_xu_ly" {{ request('trang_thai') == 'dang_xu_ly' ? 'selected' : '' }}>Đang xử lý</option>
                <option value="dang_giao" {{ request('trang_thai') == 'dang_giao' ? 'selected' : '' }}>Đang giao</option>
                <option value="hoan_thanh" {{ request('trang_thai') == 'hoan_thanh' ? 'selected' : '' }}>Hoàn thành</option>
                <option value="da_huy" {{ request('trang_thai') == 'da_huy' ? 'selected' : '' }}>Đã hủy</option>
            </select>
        </form>
    </div>

    <div class="table-card-custom">
        <div class="table-responsive">
            <table class="table-custom w-100">
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Thành tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($danhSachDonHang as $dh)
                        <tr>
                            <td class="fw-bold">{{ $dh->ma_don_hang }}</td>
                            <td>
                                <div>{{ $dh->nguoiDung->ho_ten ?? 'Khách vãng lai' }}</div>
                                <small class="text-muted">{{ $dh->nguoiDung->so_dien_thoai ?? '' }}</small>
                            </td>
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

                            <!-- Đã sửa: Chuyển $dh->created_at thành $dh->ngay_tao -->
                            <td>{{ $dh->ngay_tao ? date('d/m/Y H:i', strtotime($dh->ngay_tao)) : '' }}</td>

                            <td class="text-center">
                                <a href="{{ route('admin.orders.show', $dh->id) }}" class="btn btn-sm btn-outline-info"
                                    title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Chưa có đơn hàng nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $danhSachDonHang->links() }}
            </div>
        </div>
    @endsection
