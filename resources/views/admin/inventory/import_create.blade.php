@extends('admin.layouts.master')

@section('title', 'Tạo phiếu nhập kho - BOOK & BOX Admin')

@section('content')
	<div class="container-fluid">
		<div class="d-flex justify-content-between align-items-center mb-4">
			<div>
				<h3 class="fw-bold mb-1">Tạo phiếu nhập kho</h3>
				<p class="text-muted mb-0">Tồn kho và giá vốn được cập nhật cùng lúc khi lưu phiếu.</p>
			</div>
			<a href="{{ route('admin.inventory.imports.index') }}" class="btn btn-outline-secondary">Quay lại</a>
		</div>

		@if ($errors->any())
			<div class="alert alert-danger">
				<ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
			</div>
		@endif

		<form action="{{ route('admin.inventory.imports.store') }}" method="POST" id="import-form">
			@csrf
			<div class="row g-4">
				<div class="col-lg-8">
					<div class="table-responsive bg-white border rounded">
						<table class="table align-middle mb-0">
							<thead class="table-light">
								<tr>
									<th>Sách</th>
									<th style="width: 140px">Số lượng</th>
									<th style="width: 190px">Giá nhập / cuốn</th>
									<th style="width: 48px"></th>
								</tr>
							</thead>
							<tbody id="import-items">
								<tr class="import-item-row">
									<td>
										<select name="items[0][id_sach]" class="form-select" required>
											<option value="">Chọn sách</option>
											@foreach ($books as $book)
												<option value="{{ $book->id }}">{{ $book->tieu_de }}</option>
											@endforeach
										</select>
									</td>
									<td><input name="items[0][so_luong]" type="number" min="1" max="100000" class="form-control" required></td>
									<td><input name="items[0][don_gia_nhap]" type="number" min="0" step="1" class="form-control" required></td>
									<td><button type="button" class="btn btn-outline-danger btn-sm remove-import-row" aria-label="Xóa dòng">×</button></td>
								</tr>
							</tbody>
						</table>
					</div>
					<button type="button" id="add-import-row" class="btn btn-outline-primary btn-sm mt-3">
						<i class="bi bi-plus-lg me-1"></i>Thêm sách
					</button>
				</div>

				<div class="col-lg-4">
					<div class="bg-white border rounded p-3">
						<div class="mb-3">
							<label for="id_nha_xuat_ban" class="form-label">Nhà xuất bản</label>
							<select id="id_nha_xuat_ban" name="id_nha_xuat_ban" class="form-select">
								<option value="">Không chỉ định</option>
								@foreach ($publishers as $publisher)
									<option value="{{ $publisher->id }}">{{ $publisher->ten_nxb }}</option>
								@endforeach
							</select>
						</div>
						<div class="mb-3">
							<label for="ghi_chu" class="form-label">Ghi chú</label>
							<textarea id="ghi_chu" name="ghi_chu" class="form-control" rows="4" maxlength="500">{{ old('ghi_chu') }}</textarea>
						</div>
						<button type="submit" class="btn btn-primary w-100">Lưu phiếu nhập</button>
					</div>
				</div>
			</div>
		</form>
	</div>

	<template id="import-item-template">
		<tr class="import-item-row">
			<td>
				<select data-name="id_sach" class="form-select" required>
					<option value="">Chọn sách</option>
					@foreach ($books as $book)
						<option value="{{ $book->id }}">{{ $book->tieu_de }}</option>
					@endforeach
				</select>
			</td>
			<td><input data-name="so_luong" type="number" min="1" max="100000" class="form-control" required></td>
			<td><input data-name="don_gia_nhap" type="number" min="0" step="1" class="form-control" required></td>
			<td><button type="button" class="btn btn-outline-danger btn-sm remove-import-row" aria-label="Xóa dòng">×</button></td>
		</tr>
	</template>

	<script>
		(() => {
			const rows = document.getElementById('import-items');
			const template = document.getElementById('import-item-template');
			let nextIndex = rows.querySelectorAll('.import-item-row').length;

			document.getElementById('add-import-row').addEventListener('click', () => {
				const row = template.content.firstElementChild.cloneNode(true);
				row.querySelectorAll('[data-name]').forEach((field) => {
					field.name = `items[${nextIndex}][${field.dataset.name}]`;
				});
				nextIndex += 1;
				rows.append(row);
			});

			rows.addEventListener('click', (event) => {
				if (event.target.closest('.remove-import-row') && rows.querySelectorAll('.import-item-row').length > 1) {
					event.target.closest('.import-item-row').remove();
				}
			});
		})();
	</script>
@endsection
