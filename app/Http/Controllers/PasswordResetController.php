<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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

        Password::sendResetLink($validated);

        return back()->with('status', 'Nếu email có tài khoản, hướng dẫn đặt lại mật khẩu sẽ được gửi.');
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

        return back()->withErrors(['email' => __($status)]);
    }
}
