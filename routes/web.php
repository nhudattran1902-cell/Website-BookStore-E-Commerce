<?php

use App\Http\Controllers\Admin\AdminBookController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\BookPageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. STOREFRONT PUBLIC ROUTES (Khách vãng lai)
|--------------------------------------------------------------------------
*/
// Trang chủ
Route::get('/', [HomeController::class, 'index'])->name('home');

// Route xem danh sách & chi tiết sách
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{id}', [BookController::class, 'show'])->name('books.show');

// Route tìm kiếm sách
Route::get('/search', [BookController::class, 'search'])->name('books.search');

// Trang thông tin tĩnh
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/shipping', [PageController::class, 'shipping'])->name('pages.shipping');
Route::get('/return-policy', [PageController::class, 'returnPolicy'])->name('pages.return-policy');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');

// Live Chat phía Khách hàng (Hỗ trợ cả khách vãng lai qua session_id)
Route::get('/chat/messages', [ChatController::class, 'getCustomerMessages'])->name('chat.messages');
Route::post('/chat/send', [ChatController::class, 'sendCustomerMessage'])->name('chat.send');

/*
|--------------------------------------------------------------------------
| 2. AUTHENTICATION ROUTES (Đăng nhập / Đăng ký / Đăng xuất)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| 3. ADMIN MANAGEMENT ROUTES (Yêu cầu đăng nhập + quyền Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->name('admin.')
    ->group(function () {

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

        // Quản lý Kho hàng
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');

        // Quản lý Người dùng
        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        // Quản lý Đánh giá
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/reviews/{id}/toggle', [AdminReviewController::class, 'toggleApprove'])->name('reviews.toggle');
        Route::delete('/reviews/{id}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

        // Quản lý Live Chat
        Route::get('/chat', [ChatController::class, 'adminIndex'])->name('chat.index');
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
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
        Route::get('/success/{maDonHang}', [CheckoutController::class, 'success'])->name('success');
    });

    // Gửi đánh giá sách
    Route::post('/books/{bookId}/reviews', [ReviewController::class, 'store'])->name('books.reviews.store');
});

/*
|--------------------------------------------------------------------------
| 5. CUSTOMER DASHBOARD ROUTES (Lịch sử đơn hàng & Hồ sơ cá nhân)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('user')->name('customer.')->group(function () {
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
});
    