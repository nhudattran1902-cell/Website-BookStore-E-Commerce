<div>
    <!-- Be present above all else. - Naval Ravikant -->
</div>
@extends('admin.layouts.master')

@section('title', 'Phân quyền nhân viên - BOOK & BOX')

@section('content')
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Phân quyền nhân viên</h1>
            <p class="page-subtitle mb-0">{{ $user->ho_ten }} · {{ $user->email }} · {{ $user->vaiTro->pluck('ten_vai_tro')->join(', ') }}</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Quay lại người dùng</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">Vui lòng chọn ít nhất một quyền hợp lệ để tránh khóa quyền truy cập của nhân viên.</div>
    @endif

    <form action="{{ route('admin.users.permissions.update', $user) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="row g-3">
            @foreach ($permissionGroups as $group)
                <div class="col-12 col-xl-6">
                    <section class="card h-100">
                        <div class="card-header fw-bold">{{ $group['label'] }}</div>
                        <div class="card-body">
                            @foreach ($group['permissions'] as $permission)
                                <label class="d-flex align-items-start gap-2 mb-3">
                                    <input
                                        class="form-check-input mt-1"
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission['key'] }}"
                                        @checked(in_array($permission['key'], old('permissions', $selectedPermissions), true))
                                    >
                                    <span>{{ $permission['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Hủy</a>
            <button type="submit" class="btn btn-primary">Lưu quyền</button>
        </div>
    </form>
@endsection
