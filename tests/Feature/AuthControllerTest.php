<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use App\Models\VaiTro;
use App\Notifications\AdminPasswordResetOtpNotification;
use App\Notifications\SuspiciousLoginAttemptNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_eight_character_password_and_sends_email_verification(): void
    {
        Notification::fake();

        $this->post(route('register'), [
            'ho_ten' => 'Khách Hàng',
            'email' => 'customer@example.com',
            'mat_khau' => 'short12',
            'mat_khau_confirmation' => 'short12',
        ])->assertSessionHasErrors('mat_khau');

        $this->assertDatabaseMissing('nguoi_dung', ['email' => 'customer@example.com']);

        $response = $this->post(route('register'), [
            'ho_ten' => 'Khách Hàng',
            'email' => 'customer@example.com',
            'mat_khau' => 'strongpass8',
            'mat_khau_confirmation' => 'strongpass8',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $user = NguoiDung::where('email', 'customer@example.com')->firstOrFail();

        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_customer_can_verify_email_using_signed_link(): void
    {
        Notification::fake();
        $user = NguoiDung::factory()->unverified()->createOne();
        $this->actingAs($user);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
        );

        $this->get($verificationUrl)->assertRedirect('/');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_forgot_password_sends_reset_link_and_reset_changes_password(): void
    {
        Notification::fake();
        $user = NguoiDung::factory()->createOne();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status', "Hướng dẫn đặt lại mật khẩu đã được gửi về Gmail: {$user->email}.");

        Notification::assertSentTo($user, ResetPassword::class);
        $notification = Notification::sent($user, ResetPassword::class)->first();
        $this->assertNotNull($notification);

        $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'newstrongpass8',
            'password_confirmation' => 'newstrongpass8',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('newstrongpass8', $user->fresh()->mat_khau));
    }

    public function test_unknown_email_gets_an_explicit_not_found_message(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'missing@example.com'])
            ->assertSessionHasErrors([
                'email' => 'Email missing@example.com không tồn tại trong hệ thống.',
            ]);

        Notification::assertNothingSent();
    }

    public function test_admin_is_told_when_reset_otp_was_sent_to_email(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();

        $this->post(route('password.email'), ['email' => $admin->email])
            ->assertSessionHas('status', "OTP đã được gửi về Gmail: {$admin->email}. Mã có hiệu lực trong 5 phút.");

        $this->post(route('password.email'), ['email' => $admin->email])
            ->assertSessionHas('status', "OTP đã được gửi về Gmail: {$admin->email}. Mã có hiệu lực trong 5 phút.");

        Notification::assertSentTo($admin, AdminPasswordResetOtpNotification::class);
    }

    public function test_fifth_invalid_login_locks_account_for_thirty_seconds_and_warns_user(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();
        [$attemptKey, $lockKey] = $this->loginKeys($admin);
        RateLimiter::clear($attemptKey);
        RateLimiter::clear($lockKey);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login'), [
                'email' => $admin->email,
                'mat_khau' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login'), [
            'email' => $admin->email,
            'mat_khau' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertSame(30, RateLimiter::availableIn($lockKey));
        Notification::assertSentTo($admin, SuspiciousLoginAttemptNotification::class, function (SuspiciousLoginAttemptNotification $notification): bool {
            return $notification->failedAttempts === 5
                && $notification->lockoutSeconds === 30;
        });
        $this->assertDatabaseHas('admin_login_logs', [
            'id_nguoi_dung' => $admin->id,
            'ket_qua' => 'password_failed',
        ]);
        RateLimiter::clear($attemptKey);
        RateLimiter::clear($lockKey);
    }

    public function test_login_lockout_duration_escalates_at_requested_attempt_milestones(): void
    {
        $this->freezeTime();
        $user = NguoiDung::factory()->createOne();
        [$attemptKey, $lockKey] = $this->loginKeys($user);

        foreach ([
            5 => 30,
            6 => 60,
            7 => 90,
            8 => 120,
            9 => 150,
            10 => 300,
            14 => 300,
            15 => 900,
        ] as $attempt => $expectedSeconds) {
            RateLimiter::clear($lockKey);
            while (RateLimiter::attempts($attemptKey) < $attempt - 1) {
                RateLimiter::hit($attemptKey, 86400);
            }

            $this->post(route('login'), [
                'email' => $user->email,
                'mat_khau' => 'wrong-password',
            ])->assertSessionHasErrors('email');

            $this->assertSame($expectedSeconds, RateLimiter::availableIn($lockKey));
            RateLimiter::clear($lockKey);
        }

        RateLimiter::clear($attemptKey);
    }

    public function test_login_sends_warning_at_attempts_ten_and_fifteen(): void
    {
        Notification::fake();
        $user = NguoiDung::factory()->createOne();
        [$attemptKey, $lockKey] = $this->loginKeys($user);

        foreach ([5, 10, 15] as $attempt) {
            RateLimiter::clear($lockKey);
            while (RateLimiter::attempts($attemptKey) < $attempt - 1) {
                RateLimiter::hit($attemptKey, 86400);
            }

            $this->post(route('login'), [
                'email' => $user->email,
                'mat_khau' => 'wrong-password',
            ])->assertSessionHasErrors('email');
            RateLimiter::clear($lockKey);
        }

        Notification::assertSentTo($user, SuspiciousLoginAttemptNotification::class, 3);
        RateLimiter::clear($attemptKey);
    }

    public function test_successful_login_clears_failure_counter_and_lockout(): void
    {
        $user = NguoiDung::factory()->createOne();
        [$attemptKey, $lockKey] = $this->loginKeys($user);
        RateLimiter::hit($attemptKey, 86400);

        $this->post(route('login'), [
            'email' => $user->email,
            'mat_khau' => 'password',
        ])->assertRedirect('/');

        $this->assertSame(0, RateLimiter::attempts($attemptKey));
        $this->assertSame(0, RateLimiter::attempts($lockKey));
    }

    public function test_registration_route_limits_requests(): void
    {
        RateLimiter::clear('127.0.0.1');

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('register'), [])->assertSessionHasErrors();
        }

        $this->post(route('register'), [])->assertTooManyRequests();
    }

    public function test_login_route_limits_requests(): void
    {
        RateLimiter::clear('127.0.0.1');
        $user = NguoiDung::factory()->createOne();
        [$attemptKey, $lockKey] = $this->loginKeys($user);
        RateLimiter::clear($attemptKey);
        RateLimiter::clear($lockKey);

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post(route('login'), [
                'email' => $user->email,
                'mat_khau' => 'wrong-password',
            ])->assertSessionHasErrors('email');
            RateLimiter::clear($lockKey);
        }

        $this->post(route('login'), [
            'email' => $user->email,
            'mat_khau' => 'wrong-password',
        ])->assertTooManyRequests();

        RateLimiter::clear($attemptKey);
        RateLimiter::clear($lockKey);
    }

    /**
     * @return array{string, string}
     */
    private function loginKeys(NguoiDung $user): array
    {
        $loginKey = strtolower($user->email).'|127.0.0.1';

        return [
            'login-attempts:'.$loginKey,
            'login-lock:'.$loginKey,
        ];
    }

    public function test_successful_admin_password_login_is_audited_before_two_factor_challenge(): void
    {
        $admin = $this->createAdmin();

        $this->post(route('login'), [
            'email' => $admin->email,
            'mat_khau' => 'password',
        ])->assertRedirect(route('admin.2fa.challenge'));

        $this->assertDatabaseHas('admin_login_logs', [
            'id_nguoi_dung' => $admin->id,
            'ket_qua' => 'password_accepted',
        ]);
    }

    private function createAdmin(): NguoiDung
    {
        $admin = NguoiDung::factory()->createOne();
        $role = VaiTro::create(['ten_vai_tro' => 'admin']);
        $admin->vaiTro()->attach($role);

        return $admin;
    }
}
