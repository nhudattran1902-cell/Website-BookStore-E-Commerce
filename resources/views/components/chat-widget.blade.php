<div id="chat-widget-container" class="position-fixed bottom-0 end-0 mb-4 me-4" style="z-index: 1050;">
    <button id="chat-toggle-btn" type="button"
        class="btn btn-dark rounded-circle p-3 shadow-lg d-flex align-items-center justify-content-center"
        style="width: 60px; height: 60px;" aria-label="Mở trò chuyện">
        <i class="bi bi-chat-dots-fill fs-3 text-white"></i>
    </button>

    <div id="chat-box" class="card shadow-lg border-0 rounded-3 d-none position-absolute bottom-0 end-0"
        style="width: min(380px, calc(100vw - 2rem)); height: 500px;">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
            <div class="d-flex align-items-center">
                <i class="bi bi-chat-heart-fill me-2 fs-5"></i>
                <div>
                    <strong class="d-block">BOOK & BOX</strong>
                    <small class="opacity-75">Chọn trợ lý AI hoặc nhân viên CSKH</small>
                </div>
            </div>
            <button type="button" id="chat-close-btn" class="btn-close btn-close-white btn-sm"
                aria-label="Đóng trò chuyện"></button>
        </div>

        <div class="px-3 pt-2 bg-white border-bottom">
            <ul class="nav nav-pills nav-fill gap-2" id="chat-channel-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="chatbot-tab" data-bs-toggle="pill"
                        data-bs-target="#chatbot-panel" type="button" role="tab" data-chat-channel="chatbot">
                        <i class="bi bi-robot me-1"></i> Trợ lý AI
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="support-tab" data-bs-toggle="pill"
                        data-bs-target="#support-panel" type="button" role="tab" data-chat-channel="support">
                        <i class="bi bi-headset me-1"></i> CSKH
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content flex-grow-1 overflow-hidden">
            <section id="chatbot-panel" class="tab-pane fade show active h-100" role="tabpanel">
                <div class="d-flex flex-column h-100">
                    <div id="chatbot-messages" class="flex-grow-1 overflow-auto p-3 bg-light" role="log"
                        aria-live="polite"></div>
                    <form id="chatbot-form" class="d-flex gap-2 border-top bg-white p-2">
                        @csrf
                        <input type="text" class="form-control form-control-sm rounded-pill" name="noi_dung"
                            placeholder="Hỏi AI về sách..." maxlength="1000" required autocomplete="off">
                        <button type="submit" class="btn btn-dark btn-sm rounded-circle px-2" aria-label="Gửi cho trợ lý AI">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                </div>
            </section>

            <section id="support-panel" class="tab-pane fade h-100" role="tabpanel">
                <div class="d-flex flex-column h-100">
                    <div id="support-messages" class="flex-grow-1 overflow-auto p-3 bg-light" role="log"
                        aria-live="polite"></div>
                    <form id="support-form" class="border-top bg-white p-2">
                        @csrf
                        @guest
                            <input type="text" class="form-control form-control-sm mb-2" name="ten_khach"
                                placeholder="Họ và tên của bạn" maxlength="150" required autocomplete="name">
                        @endguest
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control form-control-sm rounded-pill" name="noi_dung"
                                placeholder="Nhắn cho nhân viên CSKH..." maxlength="1000" required autocomplete="off">
                            <button type="submit" class="btn btn-primary btn-sm rounded-circle px-2" aria-label="Gửi cho CSKH">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const chatBox = document.getElementById('chat-box');
        const toggleButton = document.getElementById('chat-toggle-btn');
        const closeButton = document.getElementById('chat-close-btn');
        let activeChannel = 'chatbot';
        let supportPoller = null;

        const channels = {
            chatbot: {
                messagesUrl: @json(route('chatbot.messages')),
                sendUrl: @json(route('chatbot.send')),
                container: document.getElementById('chatbot-messages'),
                form: document.getElementById('chatbot-form'),
                greeting: 'Chào bạn! Tôi có thể tìm và tư vấn sách trong cửa hàng.',
                responder: 'Trợ lý AI',
            },
            support: {
                messagesUrl: @json(route('support.messages')),
                sendUrl: @json(route('support.send')),
                container: document.getElementById('support-messages'),
                form: document.getElementById('support-form'),
                greeting: 'Hãy để lại tin nhắn. Nhân viên CSKH sẽ phản hồi tại đây.',
                responder: 'BOOK & BOX · CSKH',
            },
        };

        function renderMessages(channelName, messages) {
            const channel = channels[channelName];
            const greeting = document.createElement('div');
            greeting.className = 'text-center text-muted my-2 small';
            greeting.textContent = channel.greeting;
            channel.container.replaceChildren(greeting);

            messages.forEach((message) => {
                const isCustomer = message.nguoi_gui === 'khach_hang';
                const row = document.createElement('div');
                row.className = `d-flex mb-2 ${isCustomer ? 'justify-content-end' : 'justify-content-start'}`;

                const bubble = document.createElement('div');
                bubble.className = `p-2 rounded-3 text-break ${isCustomer ? 'bg-dark text-white' : 'bg-white border text-dark'}`;
                bubble.style.maxWidth = isCustomer ? '82%' : '96%';
                bubble.style.fontSize = '0.875rem';

                if (!isCustomer) {
                    const label = document.createElement('small');
                    label.className = 'd-block text-primary fw-semibold mb-1';
                    label.textContent = channel.responder;
                    bubble.append(label);
                }

                const content = document.createElement('div');
                appendFormattedMessage(content, message.noi_dung);
                bubble.append(content);
                row.append(bubble);
                channel.container.append(row);
            });

            channel.container.scrollTop = channel.container.scrollHeight;
        }

        function appendFormattedMessage(container, message) {
            const lines = String(message ?? '').split('\n');
            let textLines = [];
            let lineIndex = 0;

            function flushText() {
                if (textLines.length === 0) {
                    return;
                }

                const paragraph = document.createElement('p');
                paragraph.className = 'mb-2';
                paragraph.style.whiteSpace = 'pre-line';
                paragraph.textContent = textLines.join('\n');
                container.append(paragraph);
                textLines = [];
            }

            while (lineIndex < lines.length) {
                const headers = parseMarkdownTableRow(lines[lineIndex]);
                const separators = lineIndex + 1 < lines.length
                    ? parseMarkdownTableRow(lines[lineIndex + 1])
                    : null;
                const isTable = headers !== null
                    && separators !== null
                    && separators.length === headers.length
                    && separators.every((cell) => /^:?-{3,}:?$/.test(cell));

                if (!isTable) {
                    textLines.push(lines[lineIndex]);
                    lineIndex++;
                    continue;
                }

                flushText();
                lineIndex += 2;

                const rows = [];
                while (lineIndex < lines.length) {
                    const cells = parseMarkdownTableRow(lines[lineIndex]);
                    if (cells === null || cells.length !== headers.length) {
                        break;
                    }

                    rows.push(cells);
                    lineIndex++;
                }

                appendTableCards(container, headers, rows);
            }

            flushText();
        }

        function parseMarkdownTableRow(line) {
            const trimmedLine = line.trim();
            if (!trimmedLine.includes('|')) {
                return null;
            }

            return trimmedLine
                .replace(/^\|/, '')
                .replace(/\|$/, '')
                .split('|')
                .map((cell) => cell.trim());
        }

        function appendTableCards(container, headers, rows) {
            const titleIndex = headers.findIndex((header) => /tựa đề|tiêu đề|tên sách/i.test(header));
            const cardList = document.createElement('div');
            cardList.className = 'd-grid gap-2 mb-2';

            rows.forEach((cells) => {
                const card = document.createElement('div');
                card.className = 'border rounded-2 p-2 bg-light';

                if (titleIndex >= 0) {
                    const title = document.createElement('div');
                    title.className = 'fw-semibold mb-1';
                    title.textContent = stripMarkdown(cells[titleIndex]);
                    card.append(title);
                }

                cells.forEach((value, index) => {
                    if (index === titleIndex || value === '') {
                        return;
                    }

                    const field = document.createElement('div');
                    field.className = 'd-flex justify-content-between align-items-start gap-2 small';

                    const label = document.createElement('span');
                    label.className = 'text-muted flex-shrink-0';
                    label.textContent = stripMarkdown(headers[index]);

                    const fieldValue = document.createElement('span');
                    fieldValue.className = /giá/i.test(headers[index]) ? 'text-end fw-semibold' : 'text-end';
                    fieldValue.textContent = stripMarkdown(value);

                    field.append(label, fieldValue);
                    card.append(field);
                });

                cardList.append(card);
            });

            container.append(cardList);
        }

        function stripMarkdown(value) {
            return value.replace(/\*\*(.*?)\*\*/g, '$1').replace(/__(.*?)__/g, '$1').trim();
        }

        async function loadMessages(channelName) {
            const channel = channels[channelName];

            try {
                const response = await fetch(channel.messagesUrl, { headers: { Accept: 'application/json' } });
                if (!response.ok) {
                    throw new Error('Không tải được tin nhắn. Vui lòng thử lại.');
                }

                const data = await response.json();
                renderMessages(channelName, data.messages || []);
            } catch (error) {
                channel.container.textContent = error.message;
            }
        }

        async function sendMessage(channelName, form) {
            const channel = channels[channelName];
            const submitButton = form.querySelector('button[type="submit"]');
            const payload = Object.fromEntries(new FormData(form).entries());
            submitButton.disabled = true;

            try {
                const response = await fetch(channel.sendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        Accept: 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();
                if (!response.ok) {
                    const validationMessage = Object.values(data.errors || {}).flat()[0];
                    throw new Error(validationMessage || data.message || 'Không gửi được tin nhắn.');
                }

                form.elements.noi_dung.value = '';
                await loadMessages(channelName);
            } catch (error) {
                channel.container.textContent = error.message;
            } finally {
                submitButton.disabled = false;
                form.elements.noi_dung.focus();
            }
        }

        Object.entries(channels).forEach(([channelName, channel]) => {
            channel.form.addEventListener('submit', (event) => {
                event.preventDefault();
                sendMessage(channelName, channel.form);
            });
        });

        document.querySelectorAll('[data-chat-channel]').forEach((tab) => {
            tab.addEventListener('shown.bs.tab', () => {
                activeChannel = tab.dataset.chatChannel;
                loadMessages(activeChannel);
            });
        });

        toggleButton.addEventListener('click', () => {
            chatBox.classList.toggle('d-none');
            if (chatBox.classList.contains('d-none')) {
                window.clearInterval(supportPoller);
                supportPoller = null;
                return;
            }

            loadMessages(activeChannel);
            supportPoller ??= window.setInterval(() => {
                if (activeChannel === 'support') {
                    loadMessages('support');
                }
            }, 5000);
        });

        closeButton.addEventListener('click', () => {
            chatBox.classList.add('d-none');
            window.clearInterval(supportPoller);
            supportPoller = null;
        });
    });
</script>
