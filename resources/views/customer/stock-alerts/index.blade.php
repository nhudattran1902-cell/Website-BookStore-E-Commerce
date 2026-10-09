@extends('layouts.app')

@section('title', 'Sách đang theo dõi - BOOK & BOX')

@section('content')
    <div class="container py-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h2 fw-bold mb-1">Sách đang theo dõi</h1>
                <p class="text-muted mb-0">Chúng tôi sẽ thông báo khi sách có hàng trở lại.</p>
            </div>
            <a href="{{ route('books.index') }}" class="btn btn-outline-dark">Tiếp tục xem sách</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($stockAlerts->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-bell fs-1 text-muted"></i>
                    <p class="mt-3 mb-0">Bạn chưa theo dõi sách nào sắp có hàng.</p>
                </div>
            </div>
        @else
            <div class="list-group shadow-sm">
                @foreach ($stockAlerts as $stockAlert)
                    <div class="list-group-item p-3">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $stockAlert->sach->anh_bia_url ?: asset('images/no-cover.jpg') }}"
                                    alt="{{ $stockAlert->sach->tieu_de }}" width="64" height="84"
                                    class="rounded object-fit-cover">
                                <div>
                                    <a href="{{ route('books.show', $stockAlert->sach) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $stockAlert->sach->tieu_de }}
                                    </a>
                                    @if ($stockAlert->da_thong_bao_at)
                                        <div class="small text-success mt-1">Đã gửi thông báo {{ $stockAlert->da_thong_bao_at->format('d/m/Y H:i') }}</div>
                                    @else
                                        <div class="small text-muted mt-1">Đang chờ sách có hàng</div>
                                    @endif
                                </div>
                            </div>
                            <form action="{{ route('customer.stock-alerts.destroy', $stockAlert->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Ngừng theo dõi</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $stockAlerts->links() }}</div>
        @endif
    </div>
@endsection
