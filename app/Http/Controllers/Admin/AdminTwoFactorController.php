<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Notifications\AdminLoginOtpNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminTwoFactorController extends Controller
{
    public function setup(): View|RedirectResponse
    {
        return redirect()->route('admin.2fa.challenge');
    }

    public function confirmSetup(): RedirectResponse
    {
        return redirect()->route('admin.2fa.challenge');
    }

    public function challenge(): View|RedirectResponse
    {
        $user = Auth::user();

        if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            abort(403, 'Tài khoản quản trị viên chưa có địa chỉ email hợp lệ.');
        }

        $this->sendOtpIfNeeded($user);

        return view('auth.admin-two-factor-challenge', ['email' => $user->email]);
    }

    public function resendChallengeCode(): RedirectResponse
    {
        $this->sendOtpIfNeeded(Auth::user(), true);

        return back()->with('status', 'Mã OTP mới đã được gửi đến email quản trị viên.');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $verified = DB::transaction(function () use ($data): bool {
            $user = NguoiDung::lockForUpdate()->findOrFail(Auth::id());

            if (
                ! is_string($user->admin_email_otp_hash)
                || ! $user->admin_email_otp_expires_at
                || $user->admin_email_otp_expires_at->isPast()
                || $user->admin_email_otp_attempts >= 5
            ) {
                return false;
            }

            if (! hash_equals($user->admin_email_otp_hash, hash('sha256', $data['code']))) {
                $user->admin_email_otp_attempts++;
                if ($user->admin_email_otp_attempts >= 5) {
                    $user->admin_email_otp_hash = null;
                    $user->admin_email_otp_expires_at = null;
                }
                $user->save();

                return false;
            }

            $user->admin_email_otp_hash = null;
            $user->admin_email_otp_expires_at = null;
            $user->admin_email_otp_attempts = 0;
            $user->save();

            return true;
        });

        if (! $verified) {
            return back()->withErrors(['code' => 'Mã xác thực không hợp lệ, đã dùng hoặc đã hết hạn.']);
        }

        $request->session()->regenerate();
        $request->session()->put('admin_2fa_verified_user', Auth::id());

        return redirect()->intended(route('admin.dashboard'));
    }

    private function sendOtpIfNeeded(NguoiDung $user, bool $force = false): void
    {
        $code = DB::transaction(function () use ($user, $force): ?string {
            $lockedUser = NguoiDung::lockForUpdate()->findOrFail($user->id);

            if (
                ! $force
                && is_string($lockedUser->admin_email_otp_hash)
                && $lockedUser->admin_email_otp_expires_at?->isFuture()
                && $lockedUser->admin_email_otp_attempts < 5
            ) {
                return null;
            }

            $otp = (string) random_int(100000, 999999);
            $lockedUser->admin_email_otp_hash = hash('sha256', $otp);
            $lockedUser->admin_email_otp_expires_at = now()->addMinutes(5);
            $lockedUser->admin_email_otp_attempts = 0;
            $lockedUser->save();

            return $otp;
        });

        if ($code !== null) {
            $user->notify(new AdminLoginOtpNotification($code));
        }
    }
}
