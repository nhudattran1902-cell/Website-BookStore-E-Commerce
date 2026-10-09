@extends('admin.layouts.master')

@section('title', 'Quản lý mã giảm giá - BOOK & BOX')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Quản lý mã giảm giá</h1>
            <p class="page-subtitle">Tạo mã ưu đãi, đặt điều kiện đơn hàng và theo dõi lượt sử dụng.</p>
        </div>
        <a href="{{ route('admin.discount-codes.create') }}" class="btn btn-success text-white">
            <i class="bi bi-plus-lg me-1"></i> Tạo mã giảm giá
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-card-custom">
        <div class="table-responsive">
            <table class="table-custom w-100">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Ưu đãi</th>
                        <th>Đơn tối thiểu</th>
                        <th>Lượt dùng</th>
                        <th>Thời hạn</th>
                        <th>Trạng thái</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($discountCodes as $discountCode)
                        <tr>
                            <td class="fw-bold"><code>{{ $discountCode->ma_code }}</code></td>
                            <td>
                                @if ($discountCode->loai_giam === 'phan_tram')
                                    {{ $discountCode->gia_tri }}%{{ $discountCode->gia_tri_toi_da ? ' · tối đa '.number_format($discountCode->gia_tri_toi_da, 0, ',', '.').' đ' : '' }}
                                @else
                                    {{ number_format($discountCode->gia_tri, 0, ',', '.') }} đ
                                @endif
                            </td>
                            <td>{{ number_format($discountCode->don_toi_thieu, 0, ',', '.') }} đ</td>
                            <td>{{ number_format($discountCode->da_su_dung) }} / {{ $discountCode->gioi_han_luot ?? '∞' }}</td>
                            <td>
                                {{ $discountCode->ngay_bat_dau?->format('d/m/Y H:i') ?? 'Ngay lập tức' }}
                                <span class="text-muted">→</span>
                                {{ $discountCode->ngay_het_han?->format('d/m/Y H:i') ?? 'Không hết hạn' }}
                            </td>
                            <td>
                                <span class="badge {{ $discountCode->dang_hoat_dong ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $discountCode->dang_hoat_dong ? 'Hoạt động' : 'Đã tắt' }}
                                </span>
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('admin.discount-codes.edit', $discountCode) }}" class="btn btn-sm btn-outline-primary" aria-label="Sửa mã {{ $discountCode->ma_code }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.discount-codes.destroy', $discountCode) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Bạn có chắc muốn xóa mã giảm giá này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" aria-label="Xóa mã {{ $discountCode->ma_code }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4">Chưa có mã giảm giá nào.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $discountCodes->links() }}</div>
    </div>
@endsection
