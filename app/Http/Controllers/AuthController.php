<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use App\Notifications\AdminLoginOtpNotification;
use App\Notifications\RegistrationOtpNotification;
use App\Notifications\SuspiciousLoginAttemptNotification;
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
use Throwable;

class AuthController extends Controller
{
    private const REGISTRATION_OTP_TTL_MINUTES = 10;

    private const REGISTRATION_OTP_MAX_ATTEMPTS = 5;

    private const ADMIN_LOGIN_OTP_TTL_MINUTES = 5;

    private const ADMIN_LOGIN_OTP_MAX_ATTEMPTS = 5;

    private const LOGIN_ATTEMPT_DECAY_SECONDS = 86400;

    private const LOGIN_WARNING_MILESTONES = [5, 10, 15];

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
        $attemptKey = 'login-attempts:'.$loginKey;
        $lockKey = 'login-lock:'.$loginKey;
        $user = NguoiDung::where('email', $email)->first();

        if (RateLimiter::tooManyAttempts($lockKey, 1)) {
            $this->recordUserLoginAttempt($user, $request, 'locked');
            $this->recordAdminLoginAttempt($user, $request, 'locked');

            throw ValidationException::withMessages([
                'email' => 'Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau '.RateLimiter::availableIn($lockKey).' giây.',
            ]);
        }

        if ($user && Hash::check($credentials['mat_khau'], $user->mat_khau)) {
            if (! $user->dang_hoat_dong) {
                $this->recordAdminLoginAttempt($user, $request, 'account_disabled');
                $this->recordUserLoginAttempt($user, $request, 'account_disabled');

                throw ValidationException::withMessages([
                    'email' => 'Tài khoản đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên.',
                ]);
            }

            if (is_string($user->registration_otp_hash)) {
                $request->session()->regenerate();
                $request->session()->put('registration_otp_user_id', $user->id);

                return redirect()->route('registration.otp')
                    ->withErrors(['code' => 'Vui lòng xác minh mã OTP đăng ký trước khi đăng nhập.']);
            }

            RateLimiter::clear($attemptKey);
            RateLimiter::clear($lockKey);

            if ($user->isStaffMember()) {
                $otpStatus = $this->sendAdminLoginOtp($user, $request);

                if ($otpStatus !== 'sent') {
                    $this->recordAdminLoginAttempt($user, $request, 'otp_'.$otpStatus);

                    $message = $otpStatus === 'rate_limited'
                        ? 'Bạn đã yêu cầu mã OTP quá nhiều lần. Vui lòng chờ 15 phút rồi thử lại.'
                        : 'Không thể gửi mã OTP lúc này. Vui lòng kiểm tra cấu hình email hoặc thử lại sau.';

                    return back()->withErrors(['email' => $message])->onlyInput('email');
                }

                $request->session()->regenerate();
                $request->session()->put('admin_login_otp_user_id', $user->id);
                $this->recordAdminLoginAttempt($user, $request, 'otp_sent');

                return redirect()->route('admin.login.otp')
                    ->with('status', 'Mã OTP đăng nhập đã được gửi đến email của bạn.');
            }

            $this->recordUserLoginAttempt($user, $request, 'password_accepted');
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        $failedAttempts = RateLimiter::hit($attemptKey, self::LOGIN_ATTEMPT_DECAY_SECONDS);
        $lockoutSeconds = $this->loginLockoutSeconds($failedAttempts);

        if ($lockoutSeconds > 0) {
            RateLimiter::hit($lockKey, $lockoutSeconds);
        }

        if ($user && in_array($failedAttempts, self::LOGIN_WARNING_MILESTONES, true)) {
            $user->notify(new SuspiciousLoginAttemptNotification(
                $failedAttempts,
                (string) $request->ip(),
                $lockoutSeconds,
            ));
        }

        $this->recordAdminLoginAttempt($user, $request, 'password_failed');
        $this->recordUserLoginAttempt($user, $request, 'password_failed');

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

        $request->session()->put('registration_otp_user_id', $user->id);
        $this->sendRegistrationOtp($user, $request);

        return redirect()->route('registration.otp');
    }

    public function showRegistrationOtp(Request $request): View|RedirectResponse
    {
        $userId = $request->session()->get('registration_otp_user_id');

        if (! $userId || ! NguoiDung::whereKey($userId)->exists()) {
            return redirect()->route('register');
        }

        return view('auth.verify-email');
    }

    public function verifyRegistrationOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);
        $userId = $request->session()->get('registration_otp_user_id');

        if (! $userId) {
            return redirect()->route('register');
        }

        $verifiedUser = DB::transaction(function () use ($userId, $validated): ?NguoiDung {
            $user = NguoiDung::lockForUpdate()->find($userId);

            if (
                ! $user
                || ! is_string($user->registration_otp_hash)
                || ! $user->registration_otp_expires_at
                || $user->registration_otp_expires_at->isPast()
                || $user->registration_otp_attempts >= self::REGISTRATION_OTP_MAX_ATTEMPTS
            ) {
                return null;
            }

            $submittedHash = hash_hmac('sha256', $validated['code'], (string) config('app.key'));

            if (! hash_equals($user->registration_otp_hash, $submittedHash)) {
                $user->registration_otp_attempts++;

                if ($user->registration_otp_attempts >= self::REGISTRATION_OTP_MAX_ATTEMPTS) {
                    $user->registration_otp_expires_at = now();
                }

                $user->save();

                return null;
            }

            $user->email_verified_at = now();
            $user->registration_otp_hash = null;
            $user->registration_otp_expires_at = null;
            $user->registration_otp_attempts = 0;
            $user->save();

            return $user;
        });

        if (! $verifiedUser) {
            return back()->withErrors([
                'code' => 'Mã OTP không hợp lệ, đã hết hạn hoặc đã vượt số lần nhập cho phép.',
            ]);
        }

        $request->session()->forget('registration_otp_user_id');
        Auth::login($verifiedUser);
        $request->session()->regenerate();

        return redirect()->intended('/')->with('success', 'Xác minh email thành công. Tài khoản đã sẵn sàng.');
    }

    public function resendRegistrationOtp(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('registration_otp_user_id');
        $user = $userId ? NguoiDung::find($userId) : null;

        if (! $user) {
            return redirect()->route('register');
        }

        if (! $this->sendRegistrationOtp($user, $request, true)) {
            return back()->withErrors([
                'code' => 'Bạn đã yêu cầu mã OTP quá nhiều lần. Vui lòng chờ 15 phút rồi thử lại.',
            ]);
        }

        return back()->with('status', 'Mã OTP xác minh đã được gửi lại đến email đăng ký.');
    }

    public function showAdminLoginOtp(Request $request): View|RedirectResponse
    {
        $userId = $request->session()->get('admin_login_otp_user_id');
        $user = $userId ? NguoiDung::find($userId) : null;

        if (! $user || ! $user->dang_hoat_dong || ! $user->isStaffMember()) {
            $request->session()->forget('admin_login_otp_user_id');

            return redirect()->route('login')
                ->withErrors(['email' => 'Phiên xác minh đăng nhập không hợp lệ. Vui lòng đăng nhập lại.']);
        }

        return view('auth.admin-login-otp', [
            'maskedEmail' => $this->maskEmail((string) $user->email),
        ]);
    }

    public function verifyAdminLoginOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);
        $userId = $request->session()->get('admin_login_otp_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        /** @var array{status: string, user: NguoiDung|null} $verification */
        $verification = DB::transaction(function () use ($userId, $validated): array {
            $user = NguoiDung::lockForUpdate()->find($userId);

            if (! $user || ! $user->dang_hoat_dong || ! $user->isStaffMember()) {
                return ['status' => 'account_unavailable', 'user' => $user];
            }

            if (
                ! is_string($user->admin_email_otp_hash)
                || ! $user->admin_email_otp_expires_at
                || $user->admin_email_otp_expires_at->isPast()
                || $user->admin_email_otp_attempts >= self::ADMIN_LOGIN_OTP_MAX_ATTEMPTS
            ) {
                return ['status' => 'expired', 'user' => $user];
            }

            $submittedHash = hash_hmac('sha256', $validated['code'], (string) config('app.key'));

            if (! hash_equals($user->admin_email_otp_hash, $submittedHash)) {
                $user->admin_email_otp_attempts++;

                if ($user->admin_email_otp_attempts >= self::ADMIN_LOGIN_OTP_MAX_ATTEMPTS) {
                    $user->admin_email_otp_expires_at = now();
                }

                $user->save();

                return [
                    'status' => $user->admin_email_otp_attempts >= self::ADMIN_LOGIN_OTP_MAX_ATTEMPTS
                        ? 'locked'
                        : 'invalid',
                    'user' => $user,
                ];
            }

            $user->admin_email_otp_hash = null;
            $user->admin_email_otp_expires_at = null;
            $user->admin_email_otp_attempts = 0;
            $user->save();

            return ['status' => 'verified', 'user' => $user];
        });

        $user = $verification['user'];

        if ($verification['status'] === 'account_unavailable') {
            $request->session()->forget('admin_login_otp_user_id');

            return redirect()->route('login')
                ->withErrors(['email' => 'Tài khoản không còn quyền đăng nhập. Vui lòng liên hệ quản trị viên.']);
        }

        if ($verification['status'] !== 'verified' || ! $user) {
            if ($user) {
                $this->recordAdminLoginAttempt($user, $request, 'otp_'.$verification['status']);
            }

            $message = $verification['status'] === 'expired'
                ? 'Mã OTP đã hết hạn. Hãy yêu cầu gửi mã mới.'
                : ($verification['status'] === 'locked'
                    ? 'Bạn đã nhập sai OTP quá số lần cho phép. Hãy yêu cầu gửi mã mới.'
                    : 'Mã OTP không chính xác. Vui lòng kiểm tra lại.');

            return back()->withErrors(['code' => $message]);
        }

        $request->session()->forget('admin_login_otp_user_id');
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('staff_login_otp_verified_user_id', $user->id);
        $this->recordUserLoginAttempt($user, $request, 'login_success');
        $this->recordAdminLoginAttempt($user, $request, 'login_success');

        return redirect()->intended('/');
    }

    public function resendAdminLoginOtp(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('admin_login_otp_user_id');
        $user = $userId ? NguoiDung::find($userId) : null;

        if (! $user || ! $user->dang_hoat_dong || ! $user->isStaffMember()) {
            $request->session()->forget('admin_login_otp_user_id');

            return redirect()->route('login');
        }

        $otpStatus = $this->sendAdminLoginOtp($user, $request);

        if ($otpStatus !== 'sent') {
            $this->recordAdminLoginAttempt($user, $request, 'otp_resend_'.$otpStatus);

            $message = $otpStatus === 'rate_limited'
                ? 'Bạn đã yêu cầu mã OTP quá nhiều lần. Vui lòng chờ 15 phút rồi thử lại.'
                : 'Không thể gửi mã OTP lúc này. Vui lòng kiểm tra cấu hình email hoặc thử lại sau.';

            return back()->withErrors(['code' => $message]);
        }

        $this->recordAdminLoginAttempt($user, $request, 'otp_resent');

        return back()->with('status', 'Mã OTP mới đã được gửi đến email của bạn.');
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
        if (! $user?->isStaffMember()) {
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

    private function recordUserLoginAttempt(?NguoiDung $user, Request $request, string $result): void
    {
        if (! $user) {
            return;
        }

        DB::table('user_login_logs')->insert([
            'id_nguoi_dung' => $user->id,
            'email' => $user->email,
            'ket_qua' => $result,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
        ]);
    }

    private function loginLockoutSeconds(int $failedAttempts): int
    {
        if ($failedAttempts >= 15) {
            return 900;
        }

        if ($failedAttempts >= 10) {
            return 300;
        }

        if ($failedAttempts >= 5) {
            return ($failedAttempts - 4) * 30;
        }

        return 0;
    }

    private function sendRegistrationOtp(NguoiDung $user, Request $request, bool $force = false): bool
    {
        $requestKey = 'registration-otp:'.hash('sha256', $user->id.'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($requestKey, 3)) {
            return false;
        }

        RateLimiter::hit($requestKey, 900);
        $code = (string) random_int(100000, 999999);
        $shouldSend = DB::transaction(function () use ($user, $code, $force): bool {
            $lockedUser = NguoiDung::lockForUpdate()->findOrFail($user->id);

            if (
                ! $force
                && is_string($lockedUser->registration_otp_hash)
                && $lockedUser->registration_otp_expires_at?->isFuture()
                && $lockedUser->registration_otp_attempts < self::REGISTRATION_OTP_MAX_ATTEMPTS
            ) {
                return false;
            }

            $lockedUser->registration_otp_hash = hash_hmac('sha256', $code, (string) config('app.key'));
            $lockedUser->registration_otp_expires_at = now()->addMinutes(self::REGISTRATION_OTP_TTL_MINUTES);
            $lockedUser->registration_otp_attempts = 0;
            $lockedUser->save();

            return true;
        });

        if ($shouldSend) {
            $user->notify(new RegistrationOtpNotification($code));
        }

        return $shouldSend;
    }

    private function sendAdminLoginOtp(NguoiDung $user, Request $request): string
    {
        $accountKey = 'admin-login-otp-account:'.$user->id;
        $ipKey = 'admin-login-otp-ip:'.hash('sha256', (string) $request->ip());

        if (RateLimiter::tooManyAttempts($accountKey, 5) || RateLimiter::tooManyAttempts($ipKey, 5)) {
            return 'rate_limited';
        }

        RateLimiter::hit($accountKey, 900);
        RateLimiter::hit($ipKey, 900);

        $code = (string) random_int(100000, 999999);
        $otpUser = DB::transaction(function () use ($user, $code): ?NguoiDung {
            $lockedUser = NguoiDung::lockForUpdate()->find($user->id);

            if (! $lockedUser || ! $lockedUser->dang_hoat_dong || ! $lockedUser->isStaffMember()) {
                return null;
            }

            $lockedUser->admin_email_otp_hash = hash_hmac('sha256', $code, (string) config('app.key'));
            $lockedUser->admin_email_otp_expires_at = now()->addMinutes(self::ADMIN_LOGIN_OTP_TTL_MINUTES);
            $lockedUser->admin_email_otp_attempts = 0;
            $lockedUser->save();

            return $lockedUser;
        });

        if (! $otpUser) {
            return 'account_unavailable';
        }

        try {
            $otpUser->notify(new AdminLoginOtpNotification($code, self::ADMIN_LOGIN_OTP_TTL_MINUTES));
        } catch (Throwable $exception) {
            $otpUser->admin_email_otp_hash = null;
            $otpUser->admin_email_otp_expires_at = null;
            $otpUser->admin_email_otp_attempts = 0;
            $otpUser->save();

            Log::error('Could not send admin login OTP.', [
                'user_id' => $otpUser->id,
                'exception' => $exception->getMessage(),
            ]);

            return 'delivery_failed';
        }

        return 'sent';
    }

    private function maskEmail(string $email): string
    {
        [$localPart, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($localPart === '' || $domain === '') {
            return 'email đã đăng ký';
        }

        return mb_substr($localPart, 0, 1).str_repeat('*', max(3, mb_strlen($localPart) - 1)).'@'.$domain;
    }
}
