@extends('admin.layouts.master')
@section('title', 'Quản lý Kho hàng')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Quản lý Kho hàng</h1>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @if ($sachChuaCoKho->isNotEmpty())
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>{{ $sachChuaCoKho->count() }} sách</strong> chưa có bản ghi kho:
            {{ $sachChuaCoKho->pluck('tieu_de')->implode(', ') }}
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Sách</th>
                            <th>Tồn kho</th>
                            <th>Ngưỡng cảnh báo</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-3">Điều chỉnh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inventory as $kho)
                            <tr class="{{ $kho->so_luong_ton <= $kho->nguong_canh_bao ? 'table-warning' : '' }}">
                                <td class="ps-3 fw-bold">{{ $kho->sach->tieu_de ?? 'Sách không tồn tại' }}</td>
                                <td>
                                    <span class="fs-5 fw-bold {{ $kho->so_luong_ton == 0 ? 'text-danger' : ($kho->so_luong_ton <= $kho->nguong_canh_bao ? 'text-warning' : 'text-success') }}">
                                        {{ $kho->so_luong_ton }}
                                    </span>
                                </td>
                                <td>{{ $kho->nguong_canh_bao }}</td>
                                <td>
                                    @if ($kho->so_luong_ton == 0)
                                        <span class="badge bg-danger">Hết hàng</span>
                                    @elseif ($kho->so_luong_ton <= $kho->nguong_canh_bao)
                                        <span class="badge bg-warning text-dark">Sắp hết</span>
                                    @else
                                        <span class="badge bg-success">Còn hàng</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalKho{{ $kho->id }}">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Điều chỉnh kho -->
                            <div class="modal fade" id="modalKho{{ $kho->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Điều chỉnh kho: {{ $kho->sach->tieu_de }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.inventory.update', $kho->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Số lượng tồn kho</label>
                                                    <input type="number" name="so_luong_ton" class="form-control"
                                                        value="{{ $kho->so_luong_ton }}" min="0" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Ngưỡng cảnh báo hàng sắp hết</label>
                                                    <input type="number" name="nguong_canh_bao" class="form-control"
                                                        value="{{ $kho->nguong_canh_bao }}" min="0">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-primary">Cập nhật kho</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Chưa có dữ liệu kho hàng.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $inventory->links() }}</div>
@endsection

