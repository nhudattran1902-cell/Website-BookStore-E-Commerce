<div>
    <!-- Well begun is half done. - Aristotle -->
</div>
@extends('admin.layouts.master')

@section('title', 'Nhật ký thao tác quản trị - BOOK & BOX')

@section('content')
    <div class="page-header mb-4">
        <h1 class="page-title">Nhật ký thao tác quản trị</h1>
        <p class="page-subtitle">Theo dõi thay đổi quyền, tồn kho, thanh toán và các thao tác nhạy cảm.</p>
    </div>

    <form method="GET" action="{{ route('admin.action-logs.index') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label" for="action">Loại thao tác</label>
                <select class="form-select" id="action" name="action">
                    <option value="">Tất cả thao tác</option>
                    @foreach ($actions as $actionCode => $actionLabel)
                        <option value="{{ $actionCode }}" @selected(request('action') === $actionCode)>{{ $actionLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label" for="email">Email nhân viên</label>
                <input class="form-control" id="email" type="email" name="email" value="{{ request('email') }}">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-outline-primary" type="submit">Lọc</button>
            </div>
        </div>
    </form>

    <div class="table-responsive bg-white border rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Thời gian</th>
                    <th>Nhân viên</th>
                    <th>Thao tác</th>
                    <th>Đối tượng</th>
                    <th>Chi tiết</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->actor_name ?? 'Tài khoản đã xóa' }}<br><small>{{ $log->actor_email }}</small></td>
                        <td><span class="badge bg-light text-dark border">{{ $log->hanh_dong_label }}</span></td>
                        <td>{{ $log->doi_tuong_display }}</td>
                        <td style="min-width: 280px; max-width: 420px;">
                            @if ($log->detail_lines !== [])
                                <ul class="list-unstyled d-flex flex-column gap-1 mb-0">
                                    @foreach ($log->detail_lines as $line)
                                        <li class="small text-break">
                                            <span class="text-muted">{{ $line['label'] }}:</span>
                                            <span class="fw-semibold">{{ $line['value'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="small text-muted">Không có chi tiết bổ sung.</span>
                            @endif
                        </td>
                        <td>{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4">Chưa có nhật ký thao tác.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $logs->links() }}</div>
@endsection
