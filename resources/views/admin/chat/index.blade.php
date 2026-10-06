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
                        <div id="admin-chat-conversations" class="list-group list-group-flush overflow-auto" style="max-height: 500px;">
                            @forelse($conversations as $conv)
                                <a href="{{ route('admin.chat.index', ['conv_id' => $conv->conv_id]) }}"
                                    class="list-group-item list-group-item-action p-3 {{ $activeConv == $conv->conv_id ? 'active' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="mb-0">
                                            {{ $conv->nguoiDung?->ho_ten ?? $conv->ten_khach ?? 'Khách vãng lai' }}
                                        </strong>
                                        <small class="text-muted"
                                            style="font-size: 0.75rem;">{{ $conv->last_msg_time }}</small>
                                    </div>
                                    <small class="text-muted d-block">Khách hàng</small>
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
                                <strong class="mb-0">Đang trò chuyện với: {{ $activeCustomerName }}</strong>
                            </div>

                            <div id="admin-chat-messages" class="flex-grow-1 p-3 overflow-auto bg-light" role="log" aria-live="polite">
                                @foreach ($messages as $msg)
                                    @php
                                        $isAdminMessage = $msg->nguoi_gui === 'admin';
                                    @endphp
                                    <div
                                        class="d-flex mb-3 {{ $isAdminMessage ? 'justify-content-end' : 'justify-content-start' }}"
                                        data-message-id="{{ $msg->id }}">
                                        <div class="p-3 rounded-3 text-break {{ $isAdminMessage ? 'bg-primary text-white' : 'bg-white border' }}"
                                            style="max-width: 70%;">
                                            @if (! $isAdminMessage)
                                                <small class="d-block text-muted fw-bold mb-1">Khách hàng</small>
                                            @endif
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const conversationsContainer = document.getElementById('admin-chat-conversations');
            const messagesContainer = document.getElementById('admin-chat-messages');
            const replyForm = document.getElementById('admin-reply-form');
            const replyInput = document.getElementById('admin-reply-input');
            const activeConversation = @json($activeConv);
            const updatesUrl = @json(route('admin.chat.updates'));
            const inboxUrl = @json(route('admin.chat.index'));
            let lastMessageId = Number(messagesContainer?.querySelector('[data-message-id]:last-of-type')?.dataset.messageId || 0);
            let isPolling = false;

            function renderConversations(conversations) {
                if (!conversationsContainer) {
                    return;
                }

                conversationsContainer.replaceChildren();

                if (conversations.length === 0) {
                    const emptyState = document.createElement('div');
                    emptyState.className = 'p-3 text-center text-muted';
                    emptyState.textContent = 'Chưa có tin nhắn nào.';
                    conversationsContainer.append(emptyState);
                    return;
                }

                conversations.forEach((conversation) => {
                    const link = document.createElement('a');
                    link.href = `${inboxUrl}?conv_id=${encodeURIComponent(conversation.id)}`;
                    link.className = `list-group-item list-group-item-action p-3 ${String(activeConversation) === conversation.id ? 'active' : ''}`;

                    const heading = document.createElement('div');
                    heading.className = 'd-flex justify-content-between align-items-center mb-1';
                    const name = document.createElement('strong');
                    name.className = 'mb-0';
                    name.textContent = conversation.name;
                    heading.append(name);

                    if (conversation.unread_count > 0) {
                        const unread = document.createElement('span');
                        unread.className = 'badge bg-primary rounded-pill';
                        unread.textContent = conversation.unread_count;
                        unread.setAttribute('aria-label', `${conversation.unread_count} tin chưa đọc`);
                        heading.append(unread);
                    }

                    const time = document.createElement('small');
                    time.className = 'text-muted d-block';
                    time.style.fontSize = '0.75rem';
                    time.textContent = conversation.last_msg_time || '';

                    link.append(heading, time);
                    conversationsContainer.append(link);
                });
            }

            function appendMessages(messages) {
                if (!messagesContainer) {
                    return;
                }

                messages.forEach((message) => {
                    const messageId = Number(message.id);
                    if (!Number.isFinite(messageId) || messageId <= lastMessageId) {
                        return;
                    }

                    const isCustomer = message.nguoi_gui === 'khach_hang';
                    const row = document.createElement('div');
                    row.className = `d-flex mb-3 ${isCustomer ? 'justify-content-start' : 'justify-content-end'}`;
                    row.dataset.messageId = messageId;

                    const bubble = document.createElement('div');
                    bubble.className = `p-3 rounded-3 text-break ${isCustomer ? 'bg-white border' : 'bg-primary text-white'}`;
                    bubble.style.maxWidth = '70%';

                    if (isCustomer) {
                        const label = document.createElement('small');
                        label.className = 'd-block fw-bold mb-1 text-muted';
                        label.textContent = 'Khách hàng';
                        bubble.append(label);
                    }

                    const content = document.createElement('p');
                    content.className = 'mb-1';
                    content.textContent = message.noi_dung;
                    bubble.append(content);

                    const timestamp = document.createElement('small');
                    timestamp.className = 'd-block text-end opacity-75';
                    timestamp.style.fontSize = '0.7rem';
                    timestamp.textContent = message.ngay_tao || '';
                    bubble.append(timestamp);

                    row.append(bubble);
                    messagesContainer.append(row);
                    lastMessageId = messageId;
                });

                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            async function pollUpdates() {
                if (isPolling) {
                    return;
                }

                isPolling = true;
                const url = new URL(updatesUrl, window.location.origin);
                if (activeConversation) {
                    url.searchParams.set('conv_id', activeConversation);
                    url.searchParams.set('after_id', String(lastMessageId));
                }

                try {
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    if (!response.ok) {
                        throw new Error('Không thể làm mới hộp thư hỗ trợ.');
                    }

                    const data = await response.json();
                    renderConversations(data.conversations || []);
                    appendMessages(data.messages || []);
                } catch (error) {
                    console.error(error);
                } finally {
                    isPolling = false;
                }
            }

            if (replyForm && replyInput) {
                replyForm.addEventListener('submit', async function(event) {
                    event.preventDefault();
                    const text = replyInput.value.trim();
                    const submitButton = replyForm.querySelector('button[type="submit"]');
                    if (!text || !submitButton) {
                        return;
                    }

                    submitButton.disabled = true;
                    try {
                        const response = await fetch(@json(route('admin.chat.reply')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': @json(csrf_token()),
                                Accept: 'application/json',
                            },
                            body: JSON.stringify({ conv_id: activeConversation, noi_dung: text }),
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'Không gửi được câu trả lời.');
                        }

                        replyInput.value = '';
                        appendMessages([data.message]);
                        pollUpdates();
                    } catch (error) {
                        window.alert(error.message);
                    } finally {
                        submitButton.disabled = false;
                        replyInput.focus();
                    }
                });
            }

            pollUpdates();
            window.setInterval(pollUpdates, 4000);
        });
    </script>
@endsection
