<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureAdminRole;
use App\Http\Middleware\EnsurePermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        // Đăng ký middleware alias 'admin' để bảo vệ các route quản trị
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'admin.role' => EnsureAdminRole::class,
            'permission' => EnsurePermission::class,
            'account.active' => EnsureAccountIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
