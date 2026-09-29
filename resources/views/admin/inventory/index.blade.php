@extends('admin.layouts.master')

@section('title', 'Quản lý Kho hàng - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Quản lý Kho hàng</h3>
                <p class="text-muted mb-0">Theo dõi số lượng tồn kho và nhập hàng nhanh</p>
            </div>
            @if (request('id_sach'))
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Xem tất cả sách
                </a>
            @endif
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Bảng kho hàng --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3" style="width: 70px;">ID</th>
                                <th>Mã sách</th>
                                <th>Tên sách</th>
                                <th>Tồn kho thực tế</th>
                                <th>Ngưỡng cảnh báo</th>
                                <th>Trạng thái</th>
                                <th class="text-end pe-3">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventory as $item)
                                @php
                                    $sl = $item->so_luong_ton;
                                    $nguong = $item->nguong_canh_bao ?? 5;
                                @endphp
                                <tr>
                                    <td class="ps-3 fw-bold">#{{ $item->id }}</td>
                                    <td><span class="badge bg-light text-dark border">#{{ $item->sach->id ?? 'N/A' }}</span>
                                    </td>
                                    <td class="fw-bold text-dark">{{ $item->sach->tieu_de ?? 'Sách đã xóa' }}</td>
                                    <td>
                                        <span
                                            class="fs-6 fw-bold {{ $sl == 0 ? 'text-danger' : ($sl <= $nguong ? 'text-warning' : 'text-success') }}">
                                            {{ $sl }} cuốn
                                        </span>
                                    </td>
                                    <td>{{ $nguong }} cuốn</td>
                                    <td>
                                        @if ($sl == 0)
                                            <span class="badge bg-danger"><i
                                                    class="bi bi-exclamation-triangle-fill me-1"></i>Hết hàng</span>
                                        @elseif ($sl <= $nguong)
                                            <span class="badge bg-warning text-dark"><i
                                                    class="bi bi-exclamation-circle me-1"></i>Sắp hết hàng</span>
                                        @else
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>An
                                                toàn</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        {{-- Nút Nhập hàng nhanh mở Modal --}}
                                        <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal"
                                            data-bs-target="#quickImportModal{{ $item->id }}">
                                            <i class="bi bi-box-arrow-in-down me-1"></i> Nhập hàng
                                        </button>

                                        {{-- Nút Sửa cấu hình kho --}}
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editInventoryModal{{ $item->id }}">
                                            <i class="bi bi-pencil-square"></i> Điều chỉnh
                                        </button>
                                    </td>
                                </tr>

                                {{-- Modal Nhập Hàng Nhanh --}}
                                <div class="modal fade" id="quickImportModal{{ $item->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.inventory.update', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title fw-bold">Nhập hàng nhanh:
                                                        {{ $item->sach->tieu_de ?? '' }}</h5>
                                                    <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-2">Tồn kho hiện tại: <strong
                                                            class="text-primary">{{ $sl }} cuốn</strong></p>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Số lượng nhập thêm <span
                                                                class="text-danger">*</span></label>
                                                        <input type="number" name="so_luong_nhap" class="form-control"
                                                            min="1" required placeholder="Nhập số lượng bổ sung...">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-success"><i
                                                            class="bi bi-plus-circle me-1"></i> Xác nhận nhập</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Modal Điều Chỉnh Trực Tiếp --}}
                                <div class="modal fade" id="editInventoryModal{{ $item->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.inventory.update', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Điều chỉnh kho:
                                                        {{ $item->sach->tieu_de ?? '' }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Số lượng tồn kho thực tế</label>
                                                        <input type="number" name="so_luong_ton" class="form-control"
                                                            value="{{ $sl }}" min="0" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Ngưỡng cảnh báo sắp hết</label>
                                                        <input type="number" name="nguong_canh_bao" class="form-control"
                                                            value="{{ $nguong }}" min="0">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Chưa có dữ liệu tồn kho.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if (method_exists($inventory, 'links'))
                <div class="card-footer bg-transparent border-0 d-flex justify-content-end pt-3">
                    {{ $inventory->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
