<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Kiểm tra người dùng có vai trò 'admin' không.
     * Nếu không → redirect về trang chủ với thông báo lỗi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->isStaffMember()) {
            abort(403, 'Tài khoản không có quyền truy cập khu vực nhân viên.');
        }

        if ((string) $request->session()->get('staff_login_otp_verified_user_id') !== (string) $request->user()->id) {
            $intendedUrl = $request->fullUrl();

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->put('url.intended', $intendedUrl);
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Phiên đăng nhập nhân viên cần xác minh OTP trước khi vào quản trị. Vui lòng đăng nhập lại.',
            ]);
        }

        return $next($request);
    }
}
