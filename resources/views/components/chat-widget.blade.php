<div id="chat-widget-container" class="position-fixed bottom-0 end-0 mb-4 me-4" style="z-index: 1050;">
    <!-- Nút bật Chat -->
    <button id="chat-toggle-btn"
        class="btn btn-dark rounded-circle p-3 shadow-lg d-flex align-items-center justify-content-center"
        style="width: 60px; height: 60px;">
        <i class="bi bi-chat-dots-fill fs-3 text-white"></i>
    </button>

    <!-- Khung Chat Pop-up -->
    <div id="chat-box" class="card shadow-lg border-0 rounded-3 d-none position-absolute bottom-0 end-0"
        style="width: 340px; height: 430px;">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
            <div class="d-flex align-items-center">
                <i class="bi bi-headset me-2 fs-5"></i>
                <strong class="mb-0">Hỗ trợ BOOK & BOX</strong>
            </div>
            <button type="button" id="chat-close-btn" class="btn-close btn-close-white btn-sm"></button>
        </div>

        <div id="chat-messages" class="card-body overflow-auto p-3 bg-light" style="height: 300px;">
            <div class="text-center text-muted my-3 small">
                <i class="bi bi-shield-check"></i> Chào bạn! Hãy gửi câu hỏi cho chúng tôi.
            </div>
        </div>

        <div class="card-footer bg-white border-0 p-2">
            <form id="chat-form" class="d-flex gap-2">
                @csrf
                <input type="text" id="chat-input" class="form-control form-control-sm rounded-pill"
                    placeholder="Nhập tin nhắn..." required autocomplete="off">
                <button type="submit" class="btn btn-dark btn-sm rounded-circle px-2">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('chat-toggle-btn');
        const closeBtn = document.getElementById('chat-close-btn');
        const chatBox = document.getElementById('chat-box');
        const chatForm = document.getElementById('chat-form');
        const chatInput = document.getElementById('chat-input');
        const messagesContainer = document.getElementById('chat-messages');

        let pollInterval = null;

        toggleBtn.addEventListener('click', () => {
            chatBox.classList.toggle('d-none');
            if (!chatBox.classList.contains('d-none')) {
                loadMessages();
                pollInterval = setInterval(loadMessages, 3000); // Tự động làm mới mỗi 3 giây
            } else {
                clearInterval(pollInterval);
            }
        });

        closeBtn.addEventListener('click', () => {
            chatBox.classList.add('d-none');
            clearInterval(pollInterval);
        });

        function loadMessages() {
            fetch('{{ route('chat.messages') }}')
                .then(res => res.json())
                .then(data => {
                    let html =
                        '<div class="text-center text-muted my-2 small"><i class="bi bi-shield-check"></i> Chào bạn! Bạn cần hỗ trợ gì?</div>';
                    data.messages.forEach(msg => {
                        const isUser = msg.nguoi_gui === 'khach_hang';
                        html += `
                        <div class="d-flex mb-2 ${isUser ? 'justify-content-end' : 'justify-content-start'}">
                            <div class="p-2 rounded-3 text-break ${isUser ? 'bg-dark text-white' : 'bg-white border text-dark'}" style="max-width: 80%; font-size: 0.875rem;">
                                ${msg.noi_dung}
                            </div>
                        </div>`;
                    });
                    messagesContainer.innerHTML = html;
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                });
        }

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = chatInput.value.trim();
            if (!text) return;

            fetch('{{ route('chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        noi_dung: text
                    })
                })
                .then(res => res.json())
                .then(() => {
                    chatInput.value = '';
                    loadMessages();
                });
        });
    });
</script>
