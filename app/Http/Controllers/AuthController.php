<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const LOGIN_MAX_ATTEMPTS = 5;

    private const LOGIN_LOCKOUT_SECONDS = 300;

    // Hiển thị form đăng nhập
    public function showLogin(): View
    {
        return view('auth.login');
    }

    // Xử lý Đăng nhập
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'mat_khau' => 'required|string',
        ]);

        $email = Str::lower(trim($credentials['email']));
        $loginKey = Str::transliterate($email.'|'.$request->ip());
        $user = NguoiDung::where('email', $email)->first();

        if (RateLimiter::tooManyAttempts($loginKey, self::LOGIN_MAX_ATTEMPTS)) {
            $this->recordAdminLoginAttempt($user, $request, 'locked');

            throw ValidationException::withMessages([
                'email' => 'Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau '.RateLimiter::availableIn($loginKey).' giây.',
            ]);
        }

        if ($user && Hash::check($credentials['mat_khau'], $user->mat_khau)) {
            RateLimiter::clear($loginKey);
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            // Nếu là Admin thì chuyển hướng tới Dashboard, ngược lại về Trang chủ
            if ($user->hasRole('admin')) {
                $this->recordAdminLoginAttempt($user, $request, 'password_accepted');

                return redirect()->route('admin.2fa.challenge');
            }

            return redirect()->intended('/');
        }

        RateLimiter::hit($loginKey, self::LOGIN_LOCKOUT_SECONDS);
        $this->recordAdminLoginAttempt($user, $request, 'password_failed');

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    // Hiển thị form đăng ký
    public function showRegister(): View
    {
        return view('auth.register');
    }

    // Xử lý Đăng ký tài khoản khách hàng
    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|string|email|max:150|unique:nguoi_dung,email',
            'mat_khau' => 'required|string|min:8|confirmed',
            'so_dien_thoai' => 'nullable|string|max:20',
        ]);

        $user = NguoiDung::create([
            'ho_ten' => $validated['ho_ten'],
            'email' => $validated['email'],
            'mat_khau' => Hash::make($validated['mat_khau']),
            'so_dien_thoai' => $validated['so_dien_thoai'] ?? null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')
            ->with('success', 'Đăng ký thành công. Vui lòng xác minh email để tiếp tục.');
    }

    // Đăng xuất
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function recordAdminLoginAttempt(?NguoiDung $user, Request $request, string $result): void
    {
        if (! $user?->hasRole('admin')) {
            return;
        }

        DB::table('admin_login_logs')->insert([
            'id_nguoi_dung' => $user->id,
            'email' => $user->email,
            'ket_qua' => $result,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
        ]);

        Log::notice('Admin login attempt recorded.', [
            'user_id' => $user->id,
            'result' => $result,
            'ip_address' => $request->ip(),
        ]);
    }
}
