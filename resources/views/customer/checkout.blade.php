@extends('layouts.app')

@section('title', 'Thanh toán đơn hàng - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="row g-4">
            {{-- Thông tin giao hàng --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h4 class="fw-bold mb-4 text-dark">
                        <i class="bi bi-geo-alt-fill text-danger me-2"></i> Thông Tin Giao Hàng
                    </h4>
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success mb-4" role="alert">{{ session('success') }}</div>
                    @endif
                    @if ($errors->hasAny(['ten_nguoi_nhan', 'sdt_nguoi_nhan', 'so_nha_duong', 'province_code', 'ward_code', 'phuong_thuc_thanh_toan', 'ghi_chu', 'cart', 'stock']))
                        <div class="alert alert-danger mb-4" role="alert">
                            <div class="fw-bold mb-1">Không thể tiếp tục thanh toán:</div>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ và tên người nhận <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="ten_nguoi_nhan" class="form-control rounded-3"
                                value="{{ old('ten_nguoi_nhan', Auth::user()->ho_ten) }}" required
                                placeholder="Nhập họ tên đầy đủ">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="sdt_nguoi_nhan" class="form-control rounded-3"
                                    value="{{ old('sdt_nguoi_nhan', Auth::user()->so_dien_thoai) }}" required
                                    placeholder="Nhập số điện thoại">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email xác nhận</label>
                                <input type="email" class="form-control rounded-3 bg-light"
                                    value="{{ Auth::user()->email }}" readonly>
                            </div>
                        </div>

                        <div class="mb-4" data-checkout-address
                            data-wards-url="{{ route('checkout.locations.wards', ['provinceCode' => 'PROVINCE_CODE']) }}">
                            <h5 class="fw-bold mb-3">Địa chỉ giao hàng <span class="text-danger">*</span></h5>
                            <div class="mb-3">
                                <label for="province_code" class="form-label fw-semibold">Tỉnh/Thành phố</label>
                                <select id="province_code" name="province_code"
                                    class="form-select rounded-3 @error('province_code') is-invalid @enderror" required>
                                    <option value="">Chọn Tỉnh/Thành phố</option>
                                    @foreach ($provinces as $provinceCode => $provinceName)
                                        <option value="{{ $provinceCode }}" @selected(old('province_code') === $provinceCode)>{{ $provinceName }}</option>
                                    @endforeach
                                </select>
                                @error('province_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="ward_code" class="form-label fw-semibold">Phường/Xã</label>
                                <select id="ward_code" name="ward_code"
                                    class="form-select rounded-3 @error('ward_code') is-invalid @enderror"
                                    data-initial-ward="{{ old('ward_code') }}" required disabled>
                                    <option value="">Chọn Tỉnh/Thành phố trước</option>
                                </select>
                                @error('ward_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="so_nha_duong" class="form-label fw-semibold">Số nhà, tên đường / thôn, ấp</label>
                                <input id="so_nha_duong" name="so_nha_duong" type="text"
                                    class="form-control rounded-3 @error('so_nha_duong') is-invalid @enderror"
                                    value="{{ old('so_nha_duong') }}" maxlength="300" required
                                    placeholder="Ví dụ: 12 Nguyễn Huệ, khu phố 1">
                                @error('so_nha_duong')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Nếu cần, ghi thêm khu vực hoặc quận/huyện cũ để shipper dễ tìm.</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Ghi chú đơn hàng (Tùy chọn)</label>
                            <textarea name="ghi_chu" class="form-control rounded-3" rows="2"
                                placeholder="Ghi chú thêm về thời gian giao hoặc hướng dẫn giao hàng">{{ old('ghi_chu') }}</textarea>
                        </div>

                        <h5 class="fw-bold mb-3 text-dark">
                            <i class="bi bi-credit-card-2-front-fill text-primary me-2"></i> Phương Thức Thanh Toán
                        </h5>

                        <div class="d-flex flex-column gap-2 mb-4">
                            <label
                                class="border rounded-3 p-3 d-flex align-items-center cursor-pointer hover-border-danger">
                                <input type="radio" name="phuong_thuc_thanh_toan" value="COD"
                                    class="form-check-input me-3" @checked(old('phuong_thuc_thanh_toan', 'COD') === 'COD')>
                                <div class="flex-grow-1">
                                    <div class="fw-bold"><i class="bi bi-cash-stack text-success me-1"></i> Thanh toán khi
                                        nhận hàng (COD)</div>
                                    <small class="text-muted">Thanh toán bằng tiền mặt trực tiếp cho shipper khi nhận
                                        sách</small>
                                </div>
                            </label>

                            <label
                                class="border rounded-3 p-3 d-flex align-items-center cursor-pointer hover-border-danger">
                                <input type="radio" name="phuong_thuc_thanh_toan" value="MoMo"
                                    class="form-check-input me-3" @checked(old('phuong_thuc_thanh_toan') === 'MoMo')>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-danger"><i class="bi bi-wallet2 me-1"></i> Ví điện tử MoMo
                                    </div>
                                    <small class="text-muted">Thanh toán an toàn qua ứng dụng Ví MoMo</small>
                                </div>
                            </label>

                            <label
                                class="border rounded-3 p-3 d-flex align-items-center cursor-pointer hover-border-danger">
                                <input type="radio" name="phuong_thuc_thanh_toan" value="VNPay"
                                    class="form-check-input me-3" @checked(old('phuong_thuc_thanh_toan') === 'VNPay')>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-primary"><i class="bi bi-qr-code-scan me-1"></i> Cổng thanh
                                        toán VNPay</div>
                                    <small class="text-muted">Thanh toán qua quét mã QR Ngân hàng / Internet Banking</small>
                                </div>
                            </label>

                            <label class="border rounded-3 p-3 d-flex align-items-center cursor-pointer hover-border-danger">
                                <input type="radio" name="phuong_thuc_thanh_toan" value="BankTransfer"
                                    class="form-check-input me-3" @checked(old('phuong_thuc_thanh_toan') === 'BankTransfer')>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-success"><i class="bi bi-bank me-1"></i> Chuyển khoản VietQR</div>
                                    <small class="text-muted">Đơn được xác nhận sau khi đối soát giao dịch ngân hàng</small>
                                </div>
                            </label>
                        </div>

                        <p class="small text-muted mb-4">Đơn thanh toán trực tuyến sẽ được giữ hàng tối đa 15 phút. Bạn sẽ chuyển tới cổng tương ứng sau khi đặt hàng.</p>

                        <button type="submit" class="btn btn-danger btn-lg w-100 rounded-pill fw-bold shadow-sm py-3">
                            <i class="bi bi-receipt me-2"></i> Đặt hàng & tiếp tục thanh toán
                        </button>
                    </form>
                </div>
            </div>

            {{-- Tóm tắt đơn hàng --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Đơn Hàng Của Bạn</h5>

                    <div class="cart-items-summary overflow-auto mb-3" style="max-height: 320px;">
                        @forelse($cartItems as $item)
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                <img src="{{ $item->sach->anh_bia_url ?: asset('images/no-cover.jpg') }}"
                                    class="rounded-2 me-3" style="width: 50px; height: 68px; object-fit: cover;">
                                <div class="flex-grow-1 pe-2">
                                    <h6 class="fw-bold mb-1 text-truncate-2 small">{{ $item->sach->tieu_de }}</h6>
                                    <small class="text-muted">Số lượng: {{ $item->so_luong }}</small>
                                </div>
                                <span class="fw-bold text-danger small">
                                    {{ number_format(($item->sach->gia_khuyen_mai ?? $item->sach->gia_ban) * $item->so_luong, 0, ',', '.') }}
                                    đ
                                </span>
                            </div>
                        @empty
                            <p class="text-muted">Không có sản phẩm nào trong giỏ.</p>
                        @endforelse
                    </div>

                    <div class="border-top pt-3">
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="fw-bold mb-2">Voucher có thể sử dụng</div>
                            @forelse ($availableVouchers as $voucher)
                                <div class="border rounded-3 p-2 mb-2">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <div class="fw-bold text-danger">{{ $voucher->ma_code }}</div>
                                            <small class="text-muted">
                                                @if ($voucher->loai_giam === 'phan_tram')
                                                    Giảm {{ $voucher->gia_tri }}%{{ $voucher->gia_tri_toi_da ? ', tối đa '.number_format($voucher->gia_tri_toi_da, 0, ',', '.').' đ' : '' }}
                                                @else
                                                    Giảm {{ number_format($voucher->gia_tri, 0, ',', '.') }} đ
                                                @endif
                                                · Đơn từ {{ number_format($voucher->don_toi_thieu, 0, ',', '.') }} đ
                                            </small>
                                            <div class="small text-success">Tiết kiệm {{ number_format($voucher->discount_preview, 0, ',', '.') }} đ</div>
                                            @if ($voucher->gioi_han_luot !== null)
                                                <small class="text-muted">Còn {{ max(0, $voucher->gioi_han_luot - $voucher->da_su_dung) }} lượt toàn hệ thống</small>
                                            @endif
                                            @if ($voucher->ngay_het_han)
                                                <small class="text-muted d-block">Hết hạn {{ $voucher->ngay_het_han->format('d/m/Y H:i') }}</small>
                                            @endif
                                        </div>
                                        <form action="{{ route('checkout.discount.apply') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="ma_code" value="{{ $voucher->ma_code }}">
                                            <button type="submit" @disabled($discountCode === $voucher->ma_code)
                                                class="btn btn-sm {{ $discountCode === $voucher->ma_code ? 'btn-success' : 'btn-outline-danger' }} text-nowrap">
                                                {{ $discountCode === $voucher->ma_code ? 'Đang dùng' : 'Dùng mã' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="small text-muted mb-2">Hiện không có voucher phù hợp với giỏ hàng này.</div>
                            @endforelse

                            <div class="small fw-semibold mt-3 mb-2">Hoặc nhập mã voucher</div>
                            <form action="{{ route('checkout.discount.apply') }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <input type="text" name="ma_code" class="form-control" maxlength="50"
                                    value="{{ old('ma_code', $discountCode) }}" placeholder="Nhập mã giảm giá" aria-label="Mã giảm giá">
                                <button type="submit" class="btn btn-outline-danger text-nowrap">Áp dụng</button>
                            </form>
                            @if ($discountCode)
                                <form action="{{ route('checkout.discount.remove') }}" method="POST" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm p-0 text-danger">Gỡ mã {{ $discountCode }}</button>
                                </form>
                            @endif
                            @if ($discountError)
                                <small class="text-danger d-block mt-2">{{ $discountError }}</small>
                            @endif
                            @error('discount_code')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tạm tính:</span>
                            <span class="fw-bold">{{ number_format($tongTien, 0, ',', '.') }} đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Phí vận chuyển:</span>
                            <span class="text-success fw-bold">Miễn phí</span>
                        </div>
                        @if ($discountAmount > 0)
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Giảm giá ({{ $discountCode }}):</span>
                                <span class="fw-bold">−{{ number_format($discountAmount, 0, ',', '.') }} đ</span>
                            </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-0">
                            <span class="fw-bold fs-5">Tổng thanh toán:</span>
                            <span class="fw-bold fs-4 text-danger">{{ number_format($total, 0, ',', '.') }} đ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addressForm = document.querySelector('[data-checkout-address]');
            const provinceSelect = document.getElementById('province_code');
            const wardSelect = document.getElementById('ward_code');

            if (!addressForm || !provinceSelect || !wardSelect) {
                return;
            }

            const initialProvince = provinceSelect.value;
            const initialWard = wardSelect.dataset.initialWard;

            const loadWards = async (restorePreviousSelection = false) => {
                wardSelect.disabled = true;
                wardSelect.innerHTML = '<option value="">Đang tải danh sách phường/xã...</option>';

                if (!provinceSelect.value) {
                    wardSelect.innerHTML = '<option value="">Chọn Tỉnh/Thành phố trước</option>';
                    return;
                }

                const endpoint = addressForm.dataset.wardsUrl.replace('PROVINCE_CODE', encodeURIComponent(provinceSelect.value));

                try {
                    const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });

                    if (!response.ok) {
                        throw new Error('Không thể tải danh sách phường/xã.');
                    }

                    const data = await response.json();
                    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';

                    data.wards.forEach((ward) => {
                        const option = document.createElement('option');
                        option.value = ward.id;
                        option.textContent = ward.name;
                        option.selected = restorePreviousSelection
                            && provinceSelect.value === initialProvince
                            && ward.id === initialWard;
                        wardSelect.appendChild(option);
                    });

                    wardSelect.disabled = data.wards.length === 0;
                } catch (error) {
                    wardSelect.innerHTML = '<option value="">Không tải được danh sách. Vui lòng chọn lại tỉnh/thành phố.</option>';
                }
            };

            provinceSelect.addEventListener('change', () => loadWards());

            if (initialProvince) {
                loadWards(true);
            }
        });
    </script>
@endpush
