@extends('admin.layouts.master')

@section('title', 'Phiếu nhập kho - BOOK & BOX Admin')

@section('content')
	<div class="container-fluid">
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h3 class="fw-bold mb-1">Phiếu nhập kho</h3>
				<p class="text-muted mb-0">Theo dõi lô hàng, nhà cung cấp và giá vốn nhập.</p>
			</div>
			@if (auth()->user()->hasPermission('inventory.import'))
				<a href="{{ route('admin.inventory.imports.create') }}" class="btn btn-primary">
					<i class="bi bi-plus-lg me-1"></i>Tạo phiếu nhập
				</a>
			@endif
		</div>

		@if (session('success'))
			<div class="alert alert-success">{{ session('success') }}</div>
		@endif

		<div class="table-responsive bg-white border rounded">
			<table class="table table-hover align-middle mb-0">
				<thead class="table-light">
					<tr>
						<th>Mã phiếu</th>
						<th>Nhà xuất bản</th>
						<th>Người nhập</th>
						<th class="text-center">Số dòng</th>
						<th class="text-end">Tổng tiền</th>
						<th>Ngày nhập</th>
					</tr>
				</thead>
				<tbody>
					@forelse ($importReceipts as $receipt)
						<tr>
							<td class="fw-semibold">{{ $receipt->ma_phieu }}</td>
							<td>{{ $receipt->nhaXuatBan->ten_nxb ?? 'Không chỉ định' }}</td>
							<td>{{ $receipt->nguoiNhap->ho_ten ?? 'Đã xóa người dùng' }}</td>
							<td class="text-center">{{ $receipt->chiTietPhieuNhap->count() }}</td>
							<td class="text-end">{{ number_format($receipt->tong_tien, 0, ',', '.') }} đ</td>
							<td>{{ $receipt->ngay_tao }}</td>
						</tr>
					@empty
						<tr>
							<td colspan="6" class="text-center text-muted py-5">Chưa có phiếu nhập kho.</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div class="d-flex justify-content-end mt-3">{{ $importReceipts->links() }}</div>
	</div>
@endsection
