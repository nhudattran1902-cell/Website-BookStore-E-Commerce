<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- 1. Top Bar Header -->
<div class="top-bar py-2 bg-light border-bottom">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Trợ giúp & Hotline / Hệ thống cửa hàng -->
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('pages.contact') }}" class="top-bar-item text-decoration-none text-dark small">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                <span class="d-none d-sm-inline">Hệ thống cửa hàng</span>
            </a>
            <div class="text-muted small d-none d-md-block">
                <i class="bi bi-telephone-fill me-1"></i> Hotline: <span class="fw-bold text-dark">+84 767417206</span>
            </div>
        </div>

        <!-- Utility Links & Auth Section -->
        <div class="d-flex align-items-center gap-3">
            <!-- Tra cứu Đơn hàng -->
            <a href="{{ Auth::check() ? route('customer.orders.index') : route('login') }}"
                class="top-bar-item text-decoration-none text-dark small">
                <i class="bi bi-truck me-1"></i>
                <span class="d-none d-sm-inline">Tra cứu đơn hàng</span>
            </a>

            <!-- Yêu thích -->
            <a href="{{ Auth::check() ? route('customer.wishlist.index') : route('login') }}" class="top-bar-item text-decoration-none text-dark small">
                <i class="bi bi-heart me-1"></i>
                <span class="d-none d-sm-inline">Yêu thích</span>
            </a>

            <!-- Giỏ hàng -->
            <a href="{{ route('cart.index') }}"
                class="top-bar-item text-decoration-none text-dark small position-relative">
                <i class="bi bi-bag me-1"></i>
                <span>Giỏ hàng</span>
                <span data-cart-count class="cart-count-badge {{ $cartCount > 0 ? '' : 'd-none' }}"
                    aria-label="{{ $cartCount }} sản phẩm trong giỏ">{{ $cartCount }}</span>
            </a>

            <!-- User / Member Section -->
            @auth
                <!-- Đã đăng nhập: Click mở Popup Thông báo / Member -->
                <button type="button"
                    class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 ms-2 d-flex align-items-center gap-1"
                    data-bs-toggle="modal" data-bs-target="#smemberNotificationModal">
                    @if (Auth::user()->anh_dai_dien_url)
                        <img src="{{ Auth::user()->anh_dai_dien_url }}"
                            alt="Ảnh đại diện của {{ Auth::user()->ho_ten }}" class="rounded-circle object-fit-cover"
                            width="26" height="26">
                    @else
                        <i class="bi bi-person-circle fs-6" aria-hidden="true"></i>
                    @endif
                    <span class="fw-bold">{{ Auth::user()->ho_ten ?? Auth::user()->ten_dang_nhap }}</span>
                    <i class="bi bi-bell-fill text-warning ms-1"></i>
                </button>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1"
                        title="Đăng xuất" aria-label="Đăng xuất">
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                        <span class="d-none d-lg-inline ms-1">Đăng xuất</span>
                    </button>
                </form>
            @else
                <!-- Chưa đăng nhập -->
                <a href="{{ route('login') }}" class="btn btn-sm btn-dark rounded-pill px-3 py-1 ms-2">
                    <i class="bi bi-person me-1"></i> Đăng nhập
                </a>
            @endauth
        </div>
    </div>
</div>

<!-- 2. Header Navigation với Form tìm kiếm bo tròn chuẩn đẹp -->
<nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="navbar-brand d-flex align-items-center gap-2 me-4">
            <span class="fw-bold fs-4 text-dark">BOOK</span>
            <img src="{{ asset('images/logo.svg') }}" alt="BOOK & BOX" class="brand-logo" style="height: 35px;">
            <span class="fw-bold fs-4 text-danger">BOX</span>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
            data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Mở menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <!-- Menu Nav -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-medium">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active fw-bold text-danger' : '' }}"
                        href="{{ route('home') }}">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('books.index') ? 'active fw-bold text-danger' : '' }}"
                        href="{{ route('books.index') }}">Cửa hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pages.about') }}">Giới thiệu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('pages.contact') }}">Liên hệ</a>
                </li>
            </ul>

            <!-- Thanh tìm kiếm tích hợp (Search Bar) -->
            <form action="{{ route('books.search') }}" method="GET" id="book-search-form"
                class="d-flex flex-grow-1 mx-lg-4 my-2 my-lg-0 position-relative"
                style="max-width: 500px;" autocomplete="off">
                <div class="input-group shadow-sm rounded-pill overflow-hidden border w-100">
                    <input type="search" name="keyword" id="book-search-input"
                        class="form-control border-0 px-3 py-2 shadow-none"
                        placeholder="Tìm sách, tác giả, ISBN, nhà xuất bản..." value="{{ request('keyword', request('q')) }}"
                        role="combobox" aria-label="Tìm kiếm sách" aria-autocomplete="list" aria-haspopup="listbox"
                        aria-controls="book-search-suggestions" aria-expanded="false" required>
                    <button class="btn btn-warning px-3 border-0 d-flex align-items-center justify-content-center"
                        type="submit" id="button-search">
                        <i class="bi bi-search fs-5 text-dark"></i>
                    </button>
                </div>
                <div id="book-search-suggestions" class="list-group position-absolute start-0 top-100 w-100 shadow d-none"
                    role="listbox" aria-label="Gợi ý sách" style="z-index: 1050; max-height: 360px; overflow-y: auto;"></div>
            </form>
</div>

@push('scripts')
    <script>
        (() => {
            const form = document.getElementById('book-search-form');
            const input = document.getElementById('book-search-input');
            const list = document.getElementById('book-search-suggestions');

            if (!form || !input || !list) {
                return;
            }

            const endpoint = @json(route('books.search.suggestions'));
            let debounceTimer;
            let abortController;
            let activeIndex = -1;

            const closeSuggestions = () => {
                list.replaceChildren();
                list.classList.add('d-none');
                input.setAttribute('aria-expanded', 'false');
                input.removeAttribute('aria-activedescendant');
                activeIndex = -1;
            };

            const setActiveIndex = (index) => {
                const options = list.querySelectorAll('[role="option"]');

                if (options.length === 0) {
                    return;
                }

                activeIndex = (index + options.length) % options.length;
                options.forEach((option, optionIndex) => {
                    const isActive = optionIndex === activeIndex;
                    option.classList.toggle('active', isActive);
                    option.setAttribute('aria-selected', String(isActive));
                });
                input.setAttribute('aria-activedescendant', options[activeIndex].id);
            };

            const renderSuggestions = (suggestions) => {
                list.replaceChildren();

                suggestions.forEach((suggestion) => {
                    const option = document.createElement('a');
                    option.href = suggestion.url;
                    option.id = `book-search-option-${suggestion.id}`;
                    option.className = 'list-group-item list-group-item-action d-flex align-items-center gap-3 py-2';
                    option.setAttribute('role', 'option');
                    option.setAttribute('aria-selected', 'false');

                    if (suggestion.cover) {
                        const cover = document.createElement('img');
                        cover.src = suggestion.cover;
                        cover.alt = '';
                        cover.width = 42;
                        cover.height = 56;
                        cover.className = 'rounded object-fit-cover flex-shrink-0';
                        option.append(cover);
                    }

                    const details = document.createElement('span');
                    details.className = 'd-flex flex-column text-truncate';

                    const title = document.createElement('strong');
                    title.className = 'text-truncate';
                    title.textContent = suggestion.title;
                    details.append(title);

                    const metadata = [suggestion.authors, suggestion.publisher, suggestion.isbn ? `ISBN ${suggestion.isbn}` : null]
                        .filter(Boolean)
                        .join(' · ');
                    if (metadata) {
                        const subtitle = document.createElement('small');
                        subtitle.className = 'text-muted text-truncate';
                        subtitle.textContent = metadata;
                        details.append(subtitle);
                    }

                    option.append(details);
                    list.append(option);
                });

                list.classList.toggle('d-none', suggestions.length === 0);
                input.setAttribute('aria-expanded', String(suggestions.length > 0));
                input.removeAttribute('aria-activedescendant');
                activeIndex = -1;
            };

            input.addEventListener('input', () => {
                window.clearTimeout(debounceTimer);
                abortController?.abort();

                const keyword = input.value.trim();
                if (keyword.length < 2) {
                    closeSuggestions();
                    return;
                }

                debounceTimer = window.setTimeout(async () => {
                    abortController = new AbortController();
                    const url = new URL(endpoint);
                    url.searchParams.set('keyword', keyword);

                    try {
                        const response = await fetch(url, {
                            headers: { Accept: 'application/json' },
                            signal: abortController.signal,
                        });

                        if (!response.ok) {
                            closeSuggestions();
                            return;
                        }

                        const suggestions = await response.json();
                        if (input.value.trim() === keyword) {
                            renderSuggestions(suggestions);
                        }
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            closeSuggestions();
                        }
                    }
                }, 250);
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown' && !list.classList.contains('d-none')) {
                    event.preventDefault();
                    setActiveIndex(activeIndex + 1);
                } else if (event.key === 'ArrowUp' && !list.classList.contains('d-none')) {
                    event.preventDefault();
                    setActiveIndex(activeIndex < 0 ? list.children.length - 1 : activeIndex - 1);
                } else if (event.key === 'Enter' && activeIndex >= 0) {
                    event.preventDefault();
                    list.querySelectorAll('[role="option"]')[activeIndex]?.click();
                } else if (event.key === 'Escape') {
                    closeSuggestions();
                }
            });

            document.addEventListener('click', (event) => {
                if (!form.contains(event.target)) {
                    closeSuggestions();
                }
            });
        })();
    </script>
@endpush
    </div>
</nav>

<!-- 3. Popup Smember / Thông báo dành cho User đã đăng nhập -->
@auth
    <div class="modal fade" id="smemberNotificationModal" tabindex="-1" aria-labelledby="smemberNotificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <!-- Modal Header -->
                <div class="modal-header bg-danger text-white rounded-top-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge-fill fs-4"></i>
                        <div>
                            <h6 class="modal-title fw-bold mb-0" id="smemberNotificationModalLabel">
                                Xin chào, {{ Auth::user()->ho_ten ?? Auth::user()->ten_dang_nhap }}!
                            </h6>
                            <small class="opacity-75">Thành viên Smember BOOK & BOX</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body với Tabs -->
                <div class="modal-body p-0">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs nav-justified border-bottom" id="smemberTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold text-dark border-0 py-3" id="all-tab"
                                data-bs-toggle="tab" data-bs-target="#all-notifications" type="button" role="tab">
                                Tất cả
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-dark border-0 py-3" id="orders-tab" data-bs-toggle="tab"
                                data-bs-target="#orders-notifications" type="button" role="tab">
                                Đơn hàng
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-dark border-0 py-3" id="cskh-tab" data-bs-toggle="tab"
                                data-bs-target="#cskh-notifications" type="button" role="tab">
                                CSKH
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content p-3" id="smemberTabContent" style="max-height: 350px; overflow-y: auto;">
                        <!-- Tab Tất cả -->
                        <div class="tab-pane fade show active" id="all-notifications" role="tabpanel">
                            @forelse ($paymentNotifications as $paymentNotification)
                                <a href="{{ route('customer.orders.show', $paymentNotification->data['order_id']) }}"
                                    class="notification-item d-block p-2 mb-2 border-bottom text-decoration-none text-dark">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-success">Thanh toán demo</span>
                                        <small class="text-muted">{{ $paymentNotification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0 small">{{ $paymentNotification->data['message'] }}</p>
                                </a>
                            @empty
                                <p class="text-muted small text-center py-4 mb-0">Bạn chưa có thông báo thanh toán.</p>
                            @endforelse
                            @foreach ($bookAvailabilityNotifications as $availabilityNotification)
                                <a href="{{ route('books.show', $availabilityNotification->data['book_id']) }}"
                                    class="notification-item d-block p-2 mb-2 border-bottom text-decoration-none text-dark">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-primary">Sách có hàng</span>
                                        <small class="text-muted">{{ $availabilityNotification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0 small">{{ $availabilityNotification->data['message'] }}</p>
                                </a>
                            @endforeach
                        </div>

                        <!-- Tab Đơn hàng -->
                        <div class="tab-pane fade" id="orders-notifications" role="tabpanel">
                            @forelse ($paymentNotifications as $paymentNotification)
                                <a href="{{ route('customer.orders.show', $paymentNotification->data['order_id']) }}"
                                    class="notification-item d-block p-2 mb-2 border-bottom text-decoration-none text-dark">
                                    <small class="text-muted d-block mb-1">{{ $paymentNotification->created_at->diffForHumans() }}</small>
                                    <span class="small">{{ $paymentNotification->data['message'] }}</span>
                                </a>
                            @empty
                                <div class="text-center py-4">
                                    <i class="bi bi-box-seam fs-1 text-muted d-block mb-2"></i>
                                    <a href="{{ route('customer.orders.index') }}" class="btn btn-sm btn-outline-danger">
                                        Xem lịch sử đơn hàng
                                    </a>
                                </div>
                            @endforelse
                        </div>

                        <!-- Tab CSKH -->
                        <div class="tab-pane fade" id="cskh-notifications" role="tabpanel">
                            <div id="customer-cskh-messages" class="d-flex flex-column gap-2" role="log" aria-live="polite">
                                <p class="text-muted small text-center mb-0">Đang tải trao đổi hỗ trợ...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-light rounded-bottom-4">
                    <a href="{{ route('customer.profile') }}" class="btn btn-sm btn-outline-secondary me-auto">
                        <i class="bi bi-gear me-1"></i> Hồ sơ
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
@endauth

@auth
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('smemberNotificationModal');
            const supportTab = document.getElementById('cskh-tab');
            const messagesContainer = document.getElementById('customer-cskh-messages');
            let refreshTimer = null;
            let isLoading = false;

            if (!modal || !supportTab || !messagesContainer) {
                return;
            }

            function renderMessages(messages) {
                messagesContainer.replaceChildren();

                if (messages.length === 0) {
                    const emptyState = document.createElement('p');
                    emptyState.className = 'text-muted small text-center mb-0 py-3';
                    emptyState.textContent = 'Bạn chưa có trao đổi với bộ phận CSKH.';
                    messagesContainer.append(emptyState);
                    return;
                }

                messages.forEach((message) => {
                    const isCustomer = message.nguoi_gui === 'khach_hang';
                    const row = document.createElement('div');
                    row.className = `p-2 border-bottom ${isCustomer ? 'text-end' : ''}`;

                    const sender = document.createElement('small');
                    sender.className = `d-block fw-semibold mb-1 ${isCustomer ? 'text-muted' : 'text-success'}`;
                    sender.textContent = isCustomer ? 'Bạn' : 'BOOK & BOX · CSKH';

                    const content = document.createElement('p');
                    content.className = 'small mb-1';
                    content.style.whiteSpace = 'pre-line';
                    content.textContent = message.noi_dung;

                    const timestamp = document.createElement('small');
                    timestamp.className = 'd-block text-muted';
                    timestamp.textContent = message.ngay_tao || '';

                    row.append(sender, content, timestamp);
                    messagesContainer.append(row);
                });

                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            async function loadSupportMessages() {
                if (isLoading) {
                    return;
                }

                isLoading = true;
                try {
                    const response = await fetch(@json(route('support.messages')), {
                        headers: { Accept: 'application/json' },
                    });

                    if (!response.ok) {
                        throw new Error('Không tải được trao đổi CSKH. Vui lòng thử lại.');
                    }

                    const data = await response.json();
                    renderMessages(data.messages || []);
                } catch (error) {
                    messagesContainer.textContent = error.message;
                } finally {
                    isLoading = false;
                }
            }

            function startRefreshing() {
                loadSupportMessages();
                if (!refreshTimer) {
                    refreshTimer = window.setInterval(loadSupportMessages, 4000);
                }
            }

            supportTab.addEventListener('shown.bs.tab', startRefreshing);
            modal.addEventListener('shown.bs.modal', () => {
                if (supportTab.classList.contains('active')) {
                    startRefreshing();
                }
            });
            modal.addEventListener('hidden.bs.modal', () => {
                if (refreshTimer) {
                    window.clearInterval(refreshTimer);
                    refreshTimer = null;
                }
            });
        });
    </script>
@endauth
