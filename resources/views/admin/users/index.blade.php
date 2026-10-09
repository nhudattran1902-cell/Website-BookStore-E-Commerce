@extends('admin.layouts.master')

@section('title', 'Quản lý Người dùng - BOOK & BOX')

@section('content')
    <div class="page-header mb-4">
        <h1 class="page-title">Danh sách Người dùng</h1>
        <p class="page-subtitle">Quản lý tài khoản, trạng thái và vai trò nhân viên trong hệ thống.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3 d-flex gap-2">
        <select name="status" class="form-select" aria-label="Lọc theo trạng thái" style="max-width: 240px;">
            <option value="all" @selected(request('status', 'all') === 'all')>Tất cả tài khoản</option>
            <option value="active" @selected(request('status') === 'active')>Đang hoạt động</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Đã vô hiệu hóa</option>
            <option value="deleted" @selected(request('status') === 'deleted')>Đã xóa</option>
        </select>
        <button class="btn btn-outline-dark" type="submit">Lọc</button>
    </form>

    <div class="table-card-custom">
        <div class="table-responsive">
            <table class="table-custom w-100">
                <thead>
                    <tr>
                        <th>Họ và tên</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Số điện thoại</th>
                        <th>Ngày tạo</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($danhSachNguoiDung as $nd)
                        <tr class="{{ $nd->trashed() ? 'table-secondary' : '' }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $nd->anh_dai_dien_url ?: asset('admin_assets/images/avatar.png') }}"
                                        class="rounded-circle" width="35" height="35" alt="Ảnh đại diện">
                                    <span class="fw-bold">{{ $nd->ho_ten }}</span>
                                </div>
                            </td>
                            <td>{{ $nd->email }}</td>
                            <td>
                                @php
                                    $roleLabels = [
                                        'admin' => 'Quản trị',
                                        'cskh' => 'CSKH',
                                        'nhan_vien_kho' => 'Nhân viên kho',
                                        'ke_toan' => 'Kế toán',
                                        'customer' => 'Khách hàng',
                                    ];
                                @endphp
                                {{ $nd->vaiTro->pluck('ten_vai_tro')->map(fn ($role) => $roleLabels[$role] ?? $role)->join(', ') ?: 'Chưa phân vai trò' }}
                            </td>
                            <td>{{ $nd->so_dien_thoai ?? 'Chưa cập nhật' }}</td>
                            <td>{{ $nd->ngay_tao ? \Carbon\Carbon::parse($nd->ngay_tao)->format('d/m/Y') : '' }}</td>
                            <td>
                                @if ($nd->trashed())
                                    <span class="badge bg-dark">Đã xóa</span>
                                @elseif ($nd->dang_hoat_dong)
                                    <span class="badge bg-success">Đang hoạt động</span>
                                @else
                                    <span class="badge bg-secondary">Đã vô hiệu hóa</span>
                                @endif
                            </td>
                            <td>
                                @if ($nd->trashed())
                                    <form action="{{ route('admin.users.restore', $nd->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success">Khôi phục</button>
                                    </form>
                                @elseif ($nd->vaiTro->contains('ten_vai_tro', 'admin'))
                                    <span class="text-muted small">Tài khoản quản trị được bảo vệ</span>
                                @else
                                    <div class="d-flex flex-column gap-2">
                                        @if ($nd->id !== auth()->id() && ! $nd->trashed())
                                            <form action="{{ route('admin.users.role', $nd) }}" method="POST" class="d-flex gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <select name="role" class="form-select form-select-sm" aria-label="Vai trò của {{ $nd->ho_ten }}">
                                                    <option value="customer" @selected($nd->vaiTro->contains('ten_vai_tro', 'customer'))>Khách hàng</option>
                                                    <option value="cskh" @selected($nd->vaiTro->contains('ten_vai_tro', 'cskh'))>CSKH</option>
                                                    <option value="nhan_vien_kho" @selected($nd->vaiTro->contains('ten_vai_tro', 'nhan_vien_kho'))>Nhân viên kho</option>
                                                    <option value="ke_toan" @selected($nd->vaiTro->contains('ten_vai_tro', 'ke_toan'))>Kế toán</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Lưu vai trò</button>
                                            </form>
                                            @if ($nd->vaiTro->contains(fn ($role) => in_array($role->ten_vai_tro, ['cskh', 'nhan_vien_kho', 'ke_toan'], true)))
                                                <a href="{{ route('admin.users.permissions.edit', $nd) }}" class="btn btn-sm btn-outline-info">Phân quyền chi tiết</a>
                                            @endif
                                        @endif
                                        <div class="d-flex gap-2">
                                        <form action="{{ route('admin.users.status', $nd) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $nd->dang_hoat_dong ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                {{ $nd->dang_hoat_dong ? 'Vô hiệu hóa' : 'Kích hoạt' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.users.destroy', $nd) }}" method="POST"
                                            onsubmit="return confirm('Xóa mềm tài khoản này? Lịch sử đơn hàng vẫn được giữ lại.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                        </form>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Chưa có người dùng nào.</td>
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
