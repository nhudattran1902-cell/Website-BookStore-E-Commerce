@extends('admin.layouts.master')

@section('title', 'Chi tiết thanh toán #' . $transaction->id . ' - BOOK & BOX Admin')

@push('styles')
    <style>
        .transaction-reconciliation {
            max-width: 980px;
            margin: 0 auto;
        }

        .transaction-reconciliation__sheet {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(33, 37, 41, .06);
            overflow: hidden;
        }

        .transaction-reconciliation__section {
            padding: 24px 32px;
            border-bottom: 1px solid #e9ecef;
        }

        .transaction-reconciliation__section:last-child {
            border-bottom: 0;
        }

        .transaction-reconciliation__label {
            color: #6c757d;
            font-size: .875rem;
        }

        .transaction-reconciliation__amount {
            font-size: 1.35rem;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 14mm;
            }

            body * {
                visibility: hidden !important;
            }

            .transaction-reconciliation,
            .transaction-reconciliation * {
                visibility: visible !important;
            }

            .transaction-reconciliation {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                max-width: none;
                margin: 0;
            }

            .transaction-reconciliation__sheet {
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .transaction-reconciliation__section {
                padding: 16px 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $order = $transaction->donHang;
        $paymentMethod = $transaction->phuong_thuc_thanh_toan ?? 'COD';
        $paymentMethods = [
            'COD' => 'COD · Thu hộ khi giao hàng',
            'MoMo' => 'Ví MoMo',
            'VNPay' => 'VNPay',
            'BankTransfer' => 'Chuyển khoản VietQR',
        ];
        $paymentStatuses = [
            'da_thanh_toan' => ['Đã thanh toán', 'success'],
            'cho_thanh_toan' => ['Chờ thanh toán', 'warning text-dark'],
            'that_bai' => ['Thanh toán thất bại', 'danger'],
            'hoan_tien' => ['Đã hoàn tiền', 'info text-dark'],
            'da_huy' => ['Đã hủy', 'secondary'],
        ];
        $paymentStatus = $paymentStatuses[$transaction->trang_thai]
            ?? [ucfirst(str_replace('_', ' ', $transaction->trang_thai)), 'secondary'];
        $expectedAmount = $order?->thanh_tien;
        $recordedAmount = $transaction->so_tien;
        $amountDifference = $expectedAmount !== null && $recordedAmount !== null
            ? (int) $recordedAmount - (int) $expectedAmount
            : null;
        $canOpenOrder = $order && auth()->user()->hasPermission('orders.view');
        $canConfirmBankTransfer = auth()->user()->hasPermission('payments.confirm')
            && $order
            && $paymentMethod === 'BankTransfer'
            && $transaction->trang_thai === 'cho_thanh_toan'
            && $order->trang_thai !== 'da_huy';
    @endphp

    <div class="container-fluid pb-4">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h1 class="h3 fw-bold mb-1">Chi tiết thanh toán #{{ $transaction->id }}</h1>
                <p class="text-muted mb-0">Giao dịch tạo lúc {{ $transaction->ngay_tao }}</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> In phiếu đối soát
                </button>
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>

        <article class="transaction-reconciliation">
            <div class="transaction-reconciliation__sheet">
                <header class="transaction-reconciliation__section">
                    <div class="row align-items-start g-3">
                        <div class="col-md-7">
                            <div class="text-uppercase text-muted small fw-bold mb-2">BOOK &amp; BOX · TÀI CHÍNH</div>
                            <h2 class="h3 fw-bold mb-1">Phiếu đối soát thanh toán</h2>
                            <div class="text-muted">Mã giao dịch nội bộ #{{ $transaction->id }}</div>
                        </div>
                        <div class="col-md-5 text-md-end">
                            <div class="transaction-reconciliation__label">Đơn hàng liên quan</div>
                            @if ($order)
                                @if ($canOpenOrder)
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="fs-5 fw-bold text-decoration-none">
                                        {{ $order->ma_don_hang ?? $order->id }}
                                    </a>
                                @else
                                    <div class="fs-5 fw-bold">{{ $order->ma_don_hang ?? $order->id }}</div>
                                @endif
                            @else
                                <div class="fs-5 fw-bold">Đơn hàng không còn tồn tại</div>
                            @endif
                            <div class="mt-2">
                                <span class="badge bg-{{ $paymentStatus[1] }}">{{ $paymentStatus[0] }}</span>
                            </div>
                        </div>
                    </div>
                </header>

                <section class="transaction-reconciliation__section">
                    <h3 class="h6 fw-bold text-uppercase mb-3">Thông tin giao dịch</h3>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="transaction-reconciliation__label">Phương thức / cổng thanh toán</div>
                            <div class="fw-semibold">{{ $paymentMethods[$paymentMethod] ?? $paymentMethod }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="transaction-reconciliation__label">Mã giao dịch bên cổng</div>
                            <div class="fw-semibold text-break">{{ $transaction->ma_giao_dich ?: 'Chưa có mã giao dịch' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="transaction-reconciliation__label">Thời điểm tạo bản ghi</div>
                            <div>{{ $transaction->ngay_tao }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="transaction-reconciliation__label">Thời điểm thanh toán</div>
                            <div>{{ $transaction->ngay_thanh_toan ?: 'Chưa ghi nhận thanh toán' }}</div>
                        </div>
                    </div>
                </section>

                <section class="transaction-reconciliation__section">
                    <h3 class="h6 fw-bold text-uppercase mb-3">Đối chiếu số tiền</h3>
                    <div class="row justify-content-end">
                        <div class="col-md-8 col-lg-7">
                            <div class="d-flex justify-content-between gap-3 mb-3">
                                <span class="text-muted">Số tiền theo đơn hàng</span>
                                <span class="fw-semibold">
                                    {{ $expectedAmount !== null ? number_format($expectedAmount, 0, ',', '.') . ' đ' : 'Không có dữ liệu đơn hàng' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center gap-3 border-top pt-3">
                                <span class="text-muted">Số tiền trong bản ghi thanh toán</span>
                                <span class="transaction-reconciliation__amount fw-bold text-dark">
                                    {{ number_format($recordedAmount ?? 0, 0, ',', '.') }} đ
                                </span>
                            </div>
                            @if ($amountDifference !== null)
                                <div class="d-flex justify-content-between align-items-center gap-3 mt-3">
                                    <span class="text-muted">Chênh lệch</span>
                                    @if ($amountDifference === 0)
                                        <span class="badge bg-success-subtle text-success-emphasis">Khớp số tiền</span>
                                    @else
                                        <span class="fw-bold text-danger">
                                            {{ $amountDifference > 0 ? '+' : '−' }}{{ number_format(abs($amountDifference), 0, ',', '.') }} đ
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                @if ($canConfirmBankTransfer)
                    <section class="transaction-reconciliation__section no-print">
                        <h3 class="h6 fw-bold text-uppercase mb-2">Xác nhận chuyển khoản</h3>
                        <p class="text-muted small">Đối chiếu số tiền và mã SIG với nội dung chuyển khoản trước khi xác nhận.</p>
                        <form action="{{ route('admin.orders.payment.confirmBankTransfer', $order->id) }}" method="POST">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="so_tien" class="form-label small">Số tiền nhận được</label>
                                    <input id="so_tien" name="so_tien" type="number" min="1" class="form-control" value="{{ $transaction->so_tien }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="ma_giao_dich" class="form-label small">Mã giao dịch ngân hàng</label>
                                    <input id="ma_giao_dich" name="ma_giao_dich" class="form-control" maxlength="100" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="signature" class="form-label small">Chữ ký SIG</label>
                                    <input id="signature" name="signature" class="form-control font-monospace" pattern="[a-fA-F0-9]{64}" maxlength="64" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success w-100">Xác nhận</button>
                                </div>
                            </div>
                        </form>
                    </section>
                @endif

                <footer class="transaction-reconciliation__section text-center text-muted small">
                    Phiếu này chỉ ghi nhận thanh toán. Xem sản phẩm, người nhận và vận chuyển trong chi tiết đơn hàng.
                </footer>
            </div>
        </article>
    </div>
@endsection
