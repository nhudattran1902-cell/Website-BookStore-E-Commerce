<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use App\Models\VaiTro;
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
            ->assertSessionHas('status');

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

    public function test_admin_login_is_temporarily_locked_after_five_invalid_passwords(): void
    {
        $admin = $this->createAdmin();
        $loginKey = strtolower($admin->email).'|127.0.0.1';
        RateLimiter::clear($loginKey);

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

        $this->assertDatabaseHas('admin_login_logs', [
            'id_nguoi_dung' => $admin->id,
            'ket_qua' => 'password_failed',
        ]);
        $this->assertDatabaseHas('admin_login_logs', [
            'id_nguoi_dung' => $admin->id,
            'ket_qua' => 'locked',
        ]);

        RateLimiter::clear($loginKey);
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
