@extends('admin.layouts.master')

@section('title', 'Nhật ký đăng nhập quản trị - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <h3 class="fw-bold mb-1">Nhật ký đăng nhập quản trị</h3>
            <p class="text-muted mb-0">Theo dõi các lần đăng nhập, địa chỉ IP và thiết bị của tài khoản quản trị.</p>
        </div>

        <form action="{{ route('admin.login-logs.index') }}" method="GET" class="card border mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email quản trị</label>
                        <input id="email" name="email" type="search" value="{{ request('email') }}"
                            class="form-control" maxlength="150" placeholder="Tìm theo email">
                    </div>
                    <div class="col-md-4">
                        <label for="ket_qua" class="form-label">Kết quả</label>
                        <select id="ket_qua" name="ket_qua" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="password_accepted" @selected(request('ket_qua') === 'password_accepted')>Mật khẩu đúng</option>
                            <option value="password_failed" @selected(request('ket_qua') === 'password_failed')>Sai mật khẩu</option>
                            <option value="locked" @selected(request('ket_qua') === 'locked')>Đang bị khóa</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">Lọc</button>
                        <a href="{{ route('admin.login-logs.index') }}" class="btn btn-outline-secondary">Xóa</a>
                    </div>
                </div>
            </div>
        </form>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">Thời gian</th>
                                <th>Email</th>
                                <th>Kết quả</th>
                                <th>Địa chỉ IP</th>
                                <th class="pe-3">Thiết bị / trình duyệt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($loginLogs as $loginLog)
                                <tr>
                                    <td class="ps-3 text-nowrap">{{ \Illuminate\Support\Carbon::parse($loginLog->attempted_at)->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $loginLog->email }}</td>
                                    <td>
                                        @if ($loginLog->ket_qua === 'password_accepted')
                                            <span class="badge bg-success">Mật khẩu đúng</span>
                                        @elseif ($loginLog->ket_qua === 'locked')
                                            <span class="badge bg-warning text-dark">Đang bị khóa</span>
                                        @else
                                            <span class="badge bg-danger">Sai mật khẩu</span>
                                        @endif
                                    </td>
                                    <td>{{ $loginLog->ip_address ?: 'Không xác định' }}</td>
                                    <td class="pe-3 text-break">{{ $loginLog->user_agent ?: 'Không xác định' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Không tìm thấy nhật ký đăng nhập.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($loginLogs->hasPages())
                <div class="card-footer bg-white">
                    {{ $loginLogs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
