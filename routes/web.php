<?php

use App\Http\Controllers\Admin\AdminActionLogController;
use App\Http\Controllers\Admin\AdminBookController;
use App\Http\Controllers\Admin\AdminLoginLogController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\BookPageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiscountCodeController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\InventoryImportController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\RevenueReportController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\CheckoutLocationController;
use App\Http\Controllers\Customer\DemoPaymentController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\PaymentGatewayController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\ReviewInteractionController;
use App\Http\Controllers\Customer\StockAlertController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. STOREFRONT PUBLIC ROUTES (Khách vãng lai)
|--------------------------------------------------------------------------
*/
// Trang chủ
Route::get('/', [HomeController::class, 'index'])->name('home');

// Route xem danh sách & chi tiết sách
Route::get('/book', [BookController::class, 'index'])->name('books.catalog');
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{id}', [BookController::class, 'show'])->name('books.show');

// Route tìm kiếm sách
Route::get('/search/suggestions', [BookController::class, 'suggestions'])
    ->middleware('throttle:120,1')
    ->name('books.search.suggestions');
Route::get('/search', [BookController::class, 'search'])->name('books.search');

// Trang thông tin tĩnh
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [ChatController::class, 'sendContactMessage'])
    ->middleware('throttle:5,1')
    ->name('pages.contact.send');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/shipping', [PageController::class, 'shipping'])->name('pages.shipping');
Route::get('/return-policy', [PageController::class, 'returnPolicy'])->name('pages.return-policy');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');

// Trợ lý AI và CSKH dùng hai luồng hội thoại độc lập.
Route::get('/chatbot/messages', [ChatController::class, 'getChatbotMessages'])->name('chatbot.messages');
Route::post('/chatbot/send', [ChatController::class, 'sendChatbotMessage'])
    ->middleware('throttle:10,1')
    ->name('chatbot.send');
Route::get('/support/messages', [ChatController::class, 'getSupportMessages'])->name('support.messages');
Route::post('/support/send', [ChatController::class, 'sendSupportMessage'])
    ->middleware('throttle:10,1')
    ->name('support.send');

/*
|--------------------------------------------------------------------------
| 2. AUTHENTICATION ROUTES (Đăng nhập / Đăng ký / Đăng xuất)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1');
    Route::get('/register/verify-otp', [AuthController::class, 'showRegistrationOtp'])->name('registration.otp');
    Route::post('/register/verify-otp', [AuthController::class, 'verifyRegistrationOtp'])->middleware('throttle:5,1')->name('registration.otp.verify');
    Route::post('/register/resend-otp', [AuthController::class, 'resendRegistrationOtp'])->middleware('throttle:3,1')->name('registration.otp.resend');
    Route::get('/admin/login/verify-otp', [AuthController::class, 'showAdminLoginOtp'])->name('admin.login.otp');
    Route::post('/admin/login/verify-otp', [AuthController::class, 'verifyAdminLoginOtp'])->middleware('throttle:5,1')->name('admin.login.otp.verify');
    Route::post('/admin/login/resend-otp', [AuthController::class, 'resendAdminLoginOtp'])->middleware('throttle:3,1')->name('admin.login.otp.resend');

    Route::get('/forgot-password', [PasswordResetController::class, 'create'])
        ->middleware('throttle:10,1')
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])
        ->middleware('throttle:10,1')
        ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| 3. ADMIN MANAGEMENT ROUTES (Yêu cầu đăng nhập + quyền Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'account.active', 'auth.session', 'admin'])
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware('admin.role:admin')
            ->name('dashboard');
        Route::get('/reports/revenue', [RevenueReportController::class, 'index'])
            ->middleware('permission:reports.revenue.view')
            ->name('reports.revenue');

        // Quản lý Sách
        Route::resource('books', AdminBookController::class)
            ->except(['show'])
            ->middleware('permission:catalog.manage');

        Route::resource('discount-codes', DiscountCodeController::class)
            ->except(['show'])
            ->middleware('permission:discounts.manage');

        // Quản lý Trang đọc thử của Sách (Preview Pages)
        Route::get('/books/{bookId}/pages', [BookPageController::class, 'index'])->middleware('permission:catalog.manage')->name('books.pages.index');
        Route::post('/books/{bookId}/pages', [BookPageController::class, 'store'])->middleware('permission:catalog.manage')->name('books.pages.store');
        Route::put('/books/{bookId}/pages/{pageId}', [BookPageController::class, 'update'])->middleware('permission:catalog.manage')->name('books.pages.update');
        Route::delete('/books/{bookId}/pages/{pageId}', [BookPageController::class, 'destroy'])->middleware('permission:catalog.manage')->name('books.pages.destroy');

        // Quản lý Thể loại
        Route::resource('categories', CategoryController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->middleware('permission:catalog.manage');

        // Quản lý Tác giả
        Route::resource('authors', AuthorController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->middleware('permission:catalog.manage');

        // Quản lý Nhà xuất bản
        Route::resource('publishers', PublisherController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->middleware('permission:catalog.manage');

        // Quản lý Đơn hàng
        Route::get('/orders', [OrderController::class, 'index'])->middleware('permission:orders.view')->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->middleware('permission:orders.view')->name('orders.show');
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus'])->middleware('permission:orders.status.update')->name('orders.updateStatus');
        Route::put('/orders/{id}/shipping', [OrderController::class, 'updateShipping'])->middleware('permission:orders.shipping.update')->name('orders.updateShipping');
        Route::post('/orders/{id}/payment/confirm-bank-transfer', [OrderController::class, 'confirmBankTransfer'])
            ->middleware('permission:payments.confirm')
            ->name('orders.payment.confirmBankTransfer');

        // Quản lý Kho hàng & Vị trí
        Route::get('/inventory', [InventoryController::class, 'index'])->middleware('permission:inventory.view')->name('inventory.index');
        Route::put('/inventory/{id}', [InventoryController::class, 'update'])->middleware('permission:inventory.update')->name('inventory.update');
        Route::post('/inventory/{id}/quick-import', [InventoryController::class, 'update'])->middleware('permission:inventory.import')->name('inventory.quick-import');

        // Quản lý Phiếu nhập kho thực tế (Bổ sung mới cho Phase 3)
        Route::get('/inventory/imports', [InventoryImportController::class, 'index'])->middleware('permission:inventory.imports.view')->name('inventory.imports.index');
        Route::get('/inventory/imports/create', [InventoryImportController::class, 'create'])->middleware('permission:inventory.import')->name('inventory.imports.create');
        Route::post('/inventory/imports', [InventoryImportController::class, 'store'])->middleware('permission:inventory.import')->name('inventory.imports.store');

        // Quản lý Người dùng
        Route::get('/users', [UserController::class, 'index'])->middleware('admin.role:admin')->name('users.index');
        Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus'])->middleware('admin.role:admin')->name('users.status');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('admin.role:admin')->name('users.destroy');
        Route::patch('/users/{id}/restore', [UserController::class, 'restore'])->middleware('admin.role:admin')->name('users.restore');
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->middleware('admin.role:admin')->name('users.role');
        Route::get('/users/{user}/permissions', [UserController::class, 'editPermissions'])->middleware('admin.role:admin')->name('users.permissions.edit');
        Route::patch('/users/{user}/permissions', [UserController::class, 'updatePermissions'])->middleware('admin.role:admin')->name('users.permissions.update');
        Route::get('/login-logs', [AdminLoginLogController::class, 'index'])->middleware('admin.role:admin')->name('login-logs.index');
        Route::get('/action-logs', [AdminActionLogController::class, 'index'])->middleware('admin.role:admin')->name('action-logs.index');

        // Quản lý Đánh giá
        Route::get('/reviews', [AdminReviewController::class, 'index'])->middleware('permission:reviews.view')->name('reviews.index');
        Route::patch('/reviews/{id}/toggle', [AdminReviewController::class, 'toggleApprove'])->middleware('permission:reviews.moderate')->name('reviews.toggle');
        Route::delete('/reviews/{id}', [AdminReviewController::class, 'destroy'])->middleware('permission:reviews.delete')->name('reviews.destroy');

        // Quản lý Live Chat
        Route::get('/chat', [ChatController::class, 'adminIndex'])->middleware('permission:chat.view')->name('chat.index');
        Route::get('/chat/updates', [ChatController::class, 'adminUpdates'])->middleware('permission:chat.view')->name('chat.updates');
        Route::post('/chat/reply', [ChatController::class, 'adminReply'])->middleware('permission:chat.reply')->name('chat.reply');

        // Quản lý Giao dịch Thanh toán
        Route::get('/transactions', [TransactionController::class, 'index'])->middleware('permission:payments.view')->name('transactions.index');
        Route::get('/transactions/{id}', [TransactionController::class, 'show'])->middleware('permission:payments.view')->name('transactions.show');
    });

/*
|--------------------------------------------------------------------------
| 4. SHOPPING CART & CHECKOUT ROUTES (Yêu cầu đăng nhập)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'account.active'])->group(function () {
    // Giỏ hàng
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'addToCart'])->name('add');
        Route::match(['post', 'put'], '/update/{id?}', [CartController::class, 'updateCart'])->name('update');
        Route::delete('/remove/{id}', [CartController::class, 'destroy'])->name('remove');
    });

    // Thanh toán
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::get('/locations/provinces', [CheckoutLocationController::class, 'provinces'])->name('locations.provinces');
        Route::get('/locations/provinces/{provinceCode}/wards', [CheckoutLocationController::class, 'wards'])->name('locations.wards');
        Route::post('/discount', [CheckoutController::class, 'applyDiscount'])->middleware('throttle:10,1')->name('discount.apply');
        Route::delete('/discount', [CheckoutController::class, 'removeDiscount'])->name('discount.remove');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
        Route::get('/success/{maDonHang}', [CheckoutController::class, 'success'])->name('success');
        Route::get('/payment/{maDonHang}', [PaymentGatewayController::class, 'start'])->name('payment.start');
        Route::post('/payment/{maDonHang}/demo', DemoPaymentController::class)->name('payment.demo');
    });

    // Gửi đánh giá sách & Tương tác
    Route::post('/books/{bookId}/reviews', [ReviewController::class, 'store'])
        ->name('books.reviews.store');
    Route::post('/reviews/{reviewId}/likes/toggle', [ReviewInteractionController::class, 'toggleLike'])
        ->middleware('throttle:60,1')
        ->name('books.reviews.likes.toggle');
    Route::post('/reviews/{reviewId}/replies', [ReviewInteractionController::class, 'storeReply'])
        ->middleware('throttle:20,1')
        ->name('books.reviews.replies.store');
});

/*
|--------------------------------------------------------------------------
| 5. CUSTOMER DASHBOARD ROUTES (Lịch sử đơn hàng & Hồ sơ cá nhân)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'account.active'])->prefix('user')->name('customer.')->group(function () {
    Route::get('/stock-alerts', [StockAlertController::class, 'index'])->name('stock-alerts.index');
    Route::post('/stock-alerts/{book}', [StockAlertController::class, 'store'])->name('stock-alerts.store');
    Route::delete('/stock-alerts/{alertId}', [StockAlertController::class, 'destroy'])->name('stock-alerts.destroy');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{book}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{book}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [CustomerOrderController::class, 'show'])->name('orders.show');

    // Thao tác Đơn hàng phía Khách hàng (Bổ sung mới cho Phase 2)
    Route::patch('/orders/{id}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
    Route::patch('/orders/{id}/update-address', [CustomerOrderController::class, 'updateAddress'])->name('orders.updateAddress');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
});
