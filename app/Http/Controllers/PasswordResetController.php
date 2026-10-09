<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = Str::lower(trim($validated['email']));
        $validated['email'] = $email;
        $requestKey = 'password-reset-request:'.hash('sha256', $email.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($requestKey, 3)) {
            return back()->withErrors([
                'email' => 'Bạn đã yêu cầu đặt lại mật khẩu quá nhiều lần. Vui lòng chờ 15 phút rồi thử lại.',
            ])->withInput($request->only('email'));
        }

        RateLimiter::hit($requestKey, 900);
        $status = Password::sendResetLink($validated);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', "Hướng dẫn đặt lại mật khẩu đã được gửi về Gmail: {$email}.");
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors([
                'email' => 'Bạn vừa yêu cầu đặt lại mật khẩu. Vui lòng chờ trước khi yêu cầu gửi lại.',
            ])->withInput($request->only('email'));
        }

        return back()->withErrors([
            'email' => 'Không thể gửi hướng dẫn đặt lại mật khẩu. Vui lòng thử lại sau.',
        ])->withInput($request->only('email'));
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'email' => $request->query('email', ''),
            'token' => $token,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = Str::lower(trim($validated['email']));
        $validated['email'] = $email;

        $status = Password::reset($validated, function (NguoiDung $user, string $password): void {
            $user->forceFill([
                'mat_khau' => Hash::make($password),
                'chuoi_nho_dang_nhap' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Đã đổi mật khẩu. Vui lòng đăng nhập lại.');
        }

        return back()->withErrors(['email' => 'Yêu cầu đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.']);
    }
}
