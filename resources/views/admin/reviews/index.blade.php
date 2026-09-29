@extends('admin.layouts.master')

@section('title', 'Quản lý Đánh giá - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid px-4 py-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Quản Lý Đánh Giá</h3>
                <p class="text-muted small mb-0">Duyệt, ẩn hoặc xóa các đánh giá từ khách hàng</p>
            </div>

            {{-- Bộ lọc trạng thái --}}
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reviews.index') }}"
                    class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-dark' }}">Tất cả</a>
                <a href="{{ route('admin.reviews.index', ['status' => 'approved']) }}"
                    class="btn btn-sm {{ request('status') === 'approved' ? 'btn-success' : 'btn-outline-success' }}">Đã
                    duyệt</a>
                <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}"
                    class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">Chờ
                    duyệt / Ẩn</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">#</th>
                                <th>Khách hàng</th>
                                <th>Sách</th>
                                <th class="text-center">Đánh giá</th>
                                <th>Nội dung bình luận</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Ngày tạo</th>
                                <th class="text-end pe-3">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $review)
                                <tr>
                                    <td class="ps-3 fw-bold">#{{ $review->id }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $review->nguoiDung->ho_ten ?? 'Khách hàng' }}
                                        </div>
                                        <small class="text-muted">{{ $review->nguoiDung->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $review->sach->tieu_de ?? 'Sách đã xóa' }}</div>
                                    </td>
                                    <td class="text-center text-warning fw-bold">
                                        {{ $review->so_sao }} <i class="bi bi-star-fill small"></i>
                                    </td>
                                    <td>
                                        <span class="small text-secondary">{{ $review->binh_luan }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($review->da_duyet)
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Đã
                                                duyệt</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill">Đã ẩn
                                                / Chờ duyệt</span>
                                        @endif
                                    </td>
                                    <td class="text-center small text-muted">
                                        {{ $review->ngay_tao }}
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex justify-content-end gap-2">
                                            {{-- Nút duyệt / ẩn --}}
                                            <form action="{{ route('admin.reviews.toggle', $review->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-sm {{ $review->da_duyet ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ $review->da_duyet ? 'Ẩn đánh giá' : 'Duyệt đánh giá' }}">
                                                    <i
                                                        class="bi {{ $review->da_duyet ? 'bi-eye-slash' : 'bi-check-lg' }}"></i>
                                                </button>
                                            </form>

                                            {{-- Nút xóa --}}
                                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST"
                                                onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Xóa đánh giá">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Không tìm thấy đánh giá nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if (method_exists($reviews, 'links'))
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-end">
                        {{ $reviews->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
