<?php

namespace App\Providers;

use App\Models\ChiTietGioHang;
use App\Models\NguoiDung;
use App\Notifications\PaymentReceivedNotification;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('components.header', function (ViewContract $view): void {
            $user = Auth::user();
            $cartCount = Auth::check()
                ? (int) ChiTietGioHang::query()
                    ->whereHas('gioHang', fn ($query) => $query->where('id_nguoi_dung', Auth::id()))
                    ->sum('so_luong')
                : 0;
            $paymentNotifications = $user instanceof NguoiDung && Schema::hasTable('notifications')
                ? $user->notifications()
                    ->where('type', PaymentReceivedNotification::class)
                    ->latest()
                    ->limit(5)
                    ->get()
                : collect();

            $view->with([
                'cartCount' => $cartCount,
                'paymentNotifications' => $paymentNotifications,
            ]);
        });

        View::composer('admin.layouts.header', function (ViewContract $view): void {
            $user = Auth::user();
            $paymentNotifications = $user instanceof NguoiDung && Schema::hasTable('notifications')
                ? $user->notifications()
                    ->where('type', PaymentReceivedNotification::class)
                    ->latest()
                    ->limit(5)
                    ->get()
                : collect();

            $view->with('paymentNotifications', $paymentNotifications);
        });
    }
}
