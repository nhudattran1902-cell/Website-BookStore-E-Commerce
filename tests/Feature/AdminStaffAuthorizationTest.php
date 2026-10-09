<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use App\Models\VaiTro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_staff_area(): void
    {
        $customer = NguoiDung::factory()->createOne();

        $this->actingAs($customer)
            ->get(route('admin.orders.index'))
            ->assertForbidden();
    }

    public function test_customer_support_can_access_customer_support_tools_only(): void
    {
        $staff = $this->createStaff('cskh');
        $this->actingAsStaffWithTwoFactor($staff);

        $this->get(route('admin.chat.index'))->assertOk();
        $this->get(route('admin.reviews.index'))->assertOk();
        $this->get(route('admin.orders.index'))->assertOk();
        $this->get(route('admin.inventory.index'))->assertOk();
        $this->put(route('admin.inventory.update', 1), ['so_luong_ton' => 10])->assertForbidden();
        $this->get(route('admin.transactions.index'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_warehouse_staff_can_access_inventory_and_shipping_tools_only(): void
    {
        $staff = $this->createStaff('nhan_vien_kho');
        $this->actingAsStaffWithTwoFactor($staff);

        $this->get(route('admin.inventory.index'))->assertOk();
        $this->get(route('admin.inventory.imports.index'))->assertOk();
        $this->get(route('admin.orders.index'))->assertOk();
        $this->get(route('admin.chat.index'))->assertForbidden();
        $this->get(route('admin.transactions.index'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_accountant_can_access_financial_tools_only(): void
    {
        $staff = $this->createStaff('ke_toan');
        $this->actingAsStaffWithTwoFactor($staff);

        $this->get(route('admin.reports.revenue'))->assertOk();
        $this->get(route('admin.transactions.index'))->assertOk();
        $this->get(route('admin.orders.index'))->assertOk();
        $this->get(route('admin.inventory.index'))->assertForbidden();
        $this->get(route('admin.chat.index'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_staff_must_verify_email_otp_and_lands_on_role_home(): void
    {
        $staff = $this->createStaff('nhan_vien_kho');

        $this->actingAs($staff)
            ->get(route('admin.inventory.index'))
            ->assertRedirect(route('admin.2fa.challenge'));
    }

    public function test_admin_can_assign_and_change_staff_roles_but_not_own_role(): void
    {
        $admin = $this->createStaff('admin');
        $customer = NguoiDung::factory()->createOne();
        $this->actingAsStaffWithTwoFactor($admin);

        $this->patch(route('admin.users.role', $customer), ['role' => 'ke_toan'])
            ->assertRedirect();
        $this->assertTrue($customer->fresh()->hasRole('ke_toan'));

        $this->patch(route('admin.users.role', $admin), ['role' => 'customer'])
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_staff_cannot_assign_roles_or_access_user_management(): void
    {
        $staff = $this->createStaff('cskh');
        $customer = NguoiDung::factory()->createOne();
        $this->actingAsStaffWithTwoFactor($staff);

        $this->get(route('admin.users.index'))->assertForbidden();
        $this->patch(route('admin.users.role', $customer), ['role' => 'ke_toan'])->assertForbidden();
        $this->assertFalse($customer->fresh()->hasRole('ke_toan'));
    }

    private function createStaff(string $roleName): NguoiDung
    {
        $staff = NguoiDung::factory()->createOne();
        $role = VaiTro::firstOrCreate(['ten_vai_tro' => $roleName]);
        $staff->vaiTro()->attach($role);

        return $staff;
    }

    private function actingAsStaffWithTwoFactor(NguoiDung $staff): self
    {
        return $this->actingAs($staff)->withSession([
            'admin_2fa_verified_user' => $staff->id,
        ]);
    }
}
