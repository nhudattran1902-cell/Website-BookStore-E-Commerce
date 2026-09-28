@extends('admin.layouts.master')

@section('title', 'Quản lý Người dùng - BOOK & BOX')

@section('content')
    <div class="page-header mb-4">
        <h1 class="page-title">Danh sách Người dùng</h1>
        <p class="page-subtitle">Tài khoản khách hàng đã đăng ký trong hệ thống.</p>
    </div>

    <div class="table-card-custom">
        <div class="table-responsive">
            <table class="table-custom w-100">
                <thead>
                    <tr>
                        <th>Họ và tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($danhSachNguoiDung as $nd)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $nd->anh_dai_dien ? asset('storage/' . $nd->anh_dai_dien) : asset('admin_assets/images/avatar.png') }}"
                                        class="rounded-circle" width="35" height="35">
                                    <span class="fw-bold">{{ $nd->ho_ten }}</span>
                                </div>
                            </td>
                            <td>{{ $nd->email }}</td>
                            <td>{{ $nd->so_dien_thoai ?? 'Chưa cập nhật' }}</td>
                            <td>{{ $nd->ngay_tao ? \Carbon\Carbon::parse($nd->ngay_tao)->format('d/m/Y') : '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Chưa có người dùng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $danhSachNguoiDung->links() }}
        </div>
    </div>
@endsection
