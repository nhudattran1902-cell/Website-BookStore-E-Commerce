<?php

namespace Tests\Feature\Admin;

use App\Models\NguoiDung;
use App\Models\VaiTro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_list_shows_account_management_controls(): void
    {
        $admin = $this->createAdmin();
        $customer = NguoiDung::factory()->createOne();

        $this->actingAsAdminWithTwoFactor($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee($customer->email)
            ->assertSee('Vô hiệu hóa')
            ->assertSee('Xóa');
    }

    public function test_admin_can_disable_customer_and_disabled_customer_cannot_log_in(): void
    {
        $admin = $this->createAdmin();
        $customer = NguoiDung::factory()->createOne();

        $this->actingAsAdminWithTwoFactor($admin)
            ->patch(route('admin.users.status', $customer))
            ->assertRedirect();

        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $customer->id,
            'dang_hoat_dong' => false,
        ]);

        $this->post('/logout');

        $this->post('/login', [
            'email' => $customer->email,
            'mat_khau' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_disabled_customer_with_existing_session_is_logged_out(): void
    {
        $customer = NguoiDung::factory()->createOne(['dang_hoat_dong' => false]);

        $this->actingAs($customer->fresh())
            ->get(route('customer.profile'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_soft_delete_and_restore_customer_without_removing_account_data(): void
    {
        $admin = $this->createAdmin();
        $customer = NguoiDung::factory()->createOne();

        $this->actingAsAdminWithTwoFactor($admin)
            ->delete(route('admin.users.destroy', $customer))
            ->assertRedirect();

        $this->assertSoftDeleted('nguoi_dung', ['id' => $customer->id]);
        $this->assertDatabaseHas('nguoi_dung', ['id' => $customer->id]);

        $this->patch(route('admin.users.restore', $customer->id))
            ->assertRedirect();

        $this->assertNotSoftDeleted('nguoi_dung', ['id' => $customer->id]);
    }

    public function test_admin_cannot_disable_or_delete_administrator_accounts(): void
    {
        $admin = $this->createAdmin();
        $otherAdmin = $this->createAdmin('other-admin@example.test');

        $this->actingAsAdminWithTwoFactor($admin)
            ->patch(route('admin.users.status', $otherAdmin))
            ->assertSessionHas('error');

        $this->delete(route('admin.users.destroy', $otherAdmin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('nguoi_dung', [
            'id' => $otherAdmin->id,
            'dang_hoat_dong' => true,
            'deleted_at' => null,
        ]);
    }

    private function createAdmin(string $email = 'admin@example.test'): NguoiDung
    {
        $admin = NguoiDung::factory()->createOne(['email' => $email]);
        $adminRole = VaiTro::firstOrCreate(['ten_vai_tro' => 'admin']);
        $admin->vaiTro()->attach($adminRole);
        $admin->forceFill([
            'admin_totp_secret' => 'JBSWY3DPEHPK3PXP',
            'admin_totp_confirmed_at' => now(),
        ])->save();

        return $admin;
    }

    private function actingAsAdminWithTwoFactor(NguoiDung $admin): self
    {
        return $this->actingAs($admin)->withSession(['admin_2fa_verified_user' => $admin->id]);
    }
}
