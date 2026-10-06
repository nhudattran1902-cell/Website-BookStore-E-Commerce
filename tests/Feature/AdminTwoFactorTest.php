<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use App\Models\VaiTro;
use App\Notifications\AdminLoginOtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_receives_email_otp_and_can_verify_it(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.2fa.challenge'));

        $this->get(route('admin.2fa.challenge'))
            ->assertOk()
            ->assertViewIs('auth.admin-two-factor-challenge')
            ->assertSee($admin->email)
            ->assertSee('Mã OTP');

        Notification::assertSentTo($admin, AdminLoginOtpNotification::class);
        $notification = Notification::sent($admin, AdminLoginOtpNotification::class)->first();
        $this->assertNotNull($notification);

        $this->from(route('admin.2fa.challenge'))
            ->post(route('admin.2fa.verify'), ['code' => $notification->code])
            ->assertRedirect(route('admin.dashboard'));

        $admin->refresh();
        $this->assertNull($admin->admin_email_otp_hash);
        $this->get(route('admin.orders.index'))->assertOk();
    }

    public function test_email_otp_cannot_be_reused_or_used_after_expiry(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $this->get(route('admin.2fa.challenge'))->assertOk();
        $notification = Notification::sent($admin, AdminLoginOtpNotification::class)->first();
        $this->assertNotNull($notification);

        $this->post(route('admin.2fa.verify'), ['code' => $notification->code])
            ->assertRedirect(route('admin.dashboard'));

        $this->post(route('admin.2fa.verify'), ['code' => $notification->code])
            ->assertSessionHasErrors('code');

        $this->post(route('admin.2fa.resend'))->assertRedirect();
        $newNotification = Notification::sent($admin, AdminLoginOtpNotification::class)->last();
        $this->assertNotNull($newNotification);
        $this->travel(6)->minutes();

        $this->post(route('admin.2fa.verify'), ['code' => $newNotification->code])
            ->assertSessionHasErrors('code');
    }

    public function test_five_incorrect_email_otp_attempts_invalidate_the_code(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();
        $this->actingAs($admin)->get(route('admin.2fa.challenge'))->assertOk();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.2fa.verify'), ['code' => '000000'])
                ->assertSessionHasErrors('code');
        }

        $this->assertNull($admin->fresh()->admin_email_otp_hash);
    }

    private function createAdmin(): NguoiDung
    {
        $admin = NguoiDung::factory()->createOne();
        $role = VaiTro::create(['ten_vai_tro' => 'admin']);
        $admin->vaiTro()->attach($role);

        return $admin;
    }
}
