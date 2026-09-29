@extends('admin.layouts.master')

@section('title', 'Quản lý Chat - BOOK & BOX Admin')

@section('content')
    <div class="container-fluid">
        <h3 class="fw-bold mb-4">Live Chat Hỗ Trợ Khách Hàng</h3>

        <div class="card border-0 shadow-sm" style="min-height: 550px;">
            <div class="card-body p-0">
                <div class="row g-0 h-100">
                    <!-- Danh sách hội thoại bên trái -->
                    <div class="col-md-4 border-end">
                        <div class="p-3 border-bottom bg-light">
                            <h6 class="fw-bold mb-0">Cuộc hội thoại</h6>
                        </div>
                        <div class="list-group list-group-flush overflow-auto" style="max-height: 500px;">
                            @forelse($conversations as $conv)
                                <a href="{{ route('admin.chat.index', ['conv_id' => $conv->conv_id]) }}"
                                    class="list-group-item list-group-item-action p-3 {{ $activeConv == $conv->conv_id ? 'active' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="mb-0">
                                            {{ $conv->nguoiDung->ho_ten ?? ($conv->nguoiDung->ten_dang_nhap ?? 'Khách #' . substr($conv->session_id, 0, 8)) }}
                                        </strong>
                                        <small class="text-muted"
                                            style="font-size: 0.75rem;">{{ $conv->last_msg_time }}</small>
                                    </div>
                                    <small class="text-truncate d-block">ID: {{ $conv->conv_id }}</small>
                                </a>
                            @empty
                                <div class="p-3 text-center text-muted">Chưa có tin nhắn nào.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Khung tin nhắn bên phải -->
                    <div class="col-md-8 d-flex flex-column" style="height: 550px;">
                        @if ($activeConv)
                            <div class="p-3 border-bottom bg-light">
                                <strong class="mb-0">Đang trò chuyện với: {{ $activeConv }}</strong>
                            </div>

                            <div id="admin-chat-messages" class="flex-grow-1 p-3 overflow-auto bg-light">
                                @foreach ($messages as $msg)
                                    <div
                                        class="d-flex mb-3 {{ $msg->nguoi_gui === 'admin' ? 'justify-content-end' : 'justify-content-start' }}">
                                        <div class="p-3 rounded-3 text-break {{ $msg->nguoi_gui === 'admin' ? 'bg-primary text-white' : 'bg-white border' }}"
                                            style="max-width: 70%;">
                                            <p class="mb-1">{{ $msg->noi_dung }}</p>
                                            <small class="d-block text-end opacity-75"
                                                style="font-size: 0.7rem;">{{ $msg->ngay_tao }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="p-3 border-top bg-white">
                                <form id="admin-reply-form" class="d-flex gap-2">
                                    @csrf
                                    <input type="hidden" name="conv_id" value="{{ $activeConv }}">
                                    <input type="text" name="noi_dung" id="admin-reply-input" class="form-control"
                                        placeholder="Nhập câu trả lời..." required>
                                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-send"></i>
                                        Gửi</button>
                                </form>
                            </div>
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                Vui lòng chọn một cuộc hội thoại bên trái để xem và trả lời.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($activeConv)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('admin-reply-form');
                const input = document.getElementById('admin-reply-input');

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const text = input.value.trim();
                    if (!text) return;

                    fetch('{{ route('admin.chat.reply') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                conv_id: '{{ $activeConv }}',
                                noi_dung: text
                            })
                        })
                        .then(res => res.json())
                        .then(() => {
                            location.reload();
                        });
                });
            });
        </script>
    @endif
@endsection
