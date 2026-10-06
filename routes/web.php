<?php

use App\Http\Controllers\Admin\AdminBookController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminTwoFactorController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\BookPageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\InventoryImportController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\DemoPaymentController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\PaymentGatewayController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\ReviewInteractionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
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

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', fn () => view('auth.verify-email'))->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->intended('/')->with('success', 'Email đã được xác minh.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('status', 'Email của bạn đã được xác minh.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Đã gửi lại liên kết xác minh email.');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| 3. ADMIN MANAGEMENT ROUTES (Yêu cầu đăng nhập + quyền Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'admin', 'admin.2fa'])
    ->name('admin.')
    ->group(function () {

        Route::get('/two-factor/setup', [AdminTwoFactorController::class, 'setup'])
            ->withoutMiddleware('admin.2fa')
            ->name('2fa.setup');
        Route::post('/two-factor/setup', [AdminTwoFactorController::class, 'confirmSetup'])
            ->withoutMiddleware('admin.2fa')
            ->middleware('throttle:5,1')
            ->name('2fa.confirm-setup');
        Route::get('/two-factor/challenge', [AdminTwoFactorController::class, 'challenge'])
            ->withoutMiddleware('admin.2fa')
            ->name('2fa.challenge');
        Route::post('/two-factor/challenge', [AdminTwoFactorController::class, 'verifyChallenge'])
            ->withoutMiddleware('admin.2fa')
            ->middleware('throttle:5,1')
            ->name('2fa.verify');
        Route::post('/two-factor/challenge/resend', [AdminTwoFactorController::class, 'resendChallengeCode'])
            ->withoutMiddleware('admin.2fa')
            ->middleware('throttle:3,1')
            ->name('2fa.resend');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Quản lý Sách
        Route::resource('books', AdminBookController::class)
            ->except(['show']);

        // Quản lý Trang đọc thử của Sách (Preview Pages)
        Route::get('/books/{bookId}/pages', [BookPageController::class, 'index'])->name('books.pages.index');
        Route::post('/books/{bookId}/pages', [BookPageController::class, 'store'])->name('books.pages.store');
        Route::put('/books/{bookId}/pages/{pageId}', [BookPageController::class, 'update'])->name('books.pages.update');
        Route::delete('/books/{bookId}/pages/{pageId}', [BookPageController::class, 'destroy'])->name('books.pages.destroy');

        // Quản lý Thể loại
        Route::resource('categories', CategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Quản lý Tác giả
        Route::resource('authors', AuthorController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Quản lý Nhà xuất bản
        Route::resource('publishers', PublisherController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Quản lý Đơn hàng
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::put('/orders/{id}/shipping', [OrderController::class, 'updateShipping'])->name('orders.updateShipping');
        Route::post('/orders/{id}/payment/confirm-bank-transfer', [OrderController::class, 'confirmBankTransfer'])
            ->name('orders.payment.confirmBankTransfer');

        // Quản lý Kho hàng & Vị trí
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');

        // Quản lý Phiếu nhập kho thực tế (Bổ sung mới cho Phase 3)
        Route::get('/inventory/imports', [InventoryImportController::class, 'index'])->name('inventory.imports.index');
        Route::get('/inventory/imports/create', [InventoryImportController::class, 'create'])->name('inventory.imports.create');
        Route::post('/inventory/imports', [InventoryImportController::class, 'store'])->name('inventory.imports.store');

        // Quản lý Người dùng
        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        // Quản lý Đánh giá
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/reviews/{id}/toggle', [AdminReviewController::class, 'toggleApprove'])->name('reviews.toggle');
        Route::delete('/reviews/{id}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

        // Quản lý Live Chat
        Route::get('/chat', [ChatController::class, 'adminIndex'])->name('chat.index');
        Route::get('/chat/updates', [ChatController::class, 'adminUpdates'])->name('chat.updates');
        Route::post('/chat/reply', [ChatController::class, 'adminReply'])->name('chat.reply');

        // Quản lý Giao dịch Thanh toán
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');
    });

/*
|--------------------------------------------------------------------------
| 4. SHOPPING CART & CHECKOUT ROUTES (Yêu cầu đăng nhập)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
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
        Route::post('/process', [CheckoutController::class, 'process'])->middleware('verified')->name('process');
        Route::get('/success/{maDonHang}', [CheckoutController::class, 'success'])->name('success');
        Route::get('/payment/{maDonHang}', [PaymentGatewayController::class, 'start'])->name('payment.start');
        Route::post('/payment/{maDonHang}/demo', DemoPaymentController::class)->name('payment.demo');
    });

    // Gửi đánh giá sách & Tương tác
    Route::post('/books/{bookId}/reviews', [ReviewController::class, 'store'])
        ->middleware('verified')
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
Route::middleware(['auth'])->prefix('user')->name('customer.')->group(function () {
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [CustomerOrderController::class, 'show'])->name('orders.show');

    // Thao tác Đơn hàng phía Khách hàng (Bổ sung mới cho Phase 2)
    Route::patch('/orders/{id}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
    Route::patch('/orders/{id}/update-address', [CustomerOrderController::class, 'updateAddress'])->name('orders.updateAddress');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
});
