<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use App\Models\VaiTro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_give_customer_support_inventory_read_access_without_edit_access(): void
    {
        $admin = $this->createUserWithRole('admin');
        $staff = $this->createUserWithRole('cskh');
        $this->actingAs($admin)->withSession(['admin_2fa_verified_user' => $admin->id]);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Phân quyền chi tiết');
        $this->get(route('admin.users.permissions.edit', $staff))
            ->assertOk()
            ->assertSee('Xem tồn kho (chỉ đọc)');

        $this->patch(route('admin.users.permissions.update', $staff), [
            'permissions' => ['inventory.view'],
        ])->assertRedirect(route('admin.users.index'));

        $this->assertSame(['inventory.view'], $staff->fresh()->quyen_han);
        $this->assertDatabaseHas('admin_action_logs', [
            'id_nguoi_thuc_hien' => $admin->id,
            'hanh_dong' => 'user.permissions.updated',
            'doi_tuong_id' => (string) $staff->id,
        ]);
        $this->get(route('admin.action-logs.index'))
            ->assertOk()
            ->assertSee('user.permissions.updated');

        $staff = $staff->fresh();
        $this->actingAs($staff)->withSession(['admin_2fa_verified_user' => $staff->id]);
        $this->get(route('admin.inventory.index'))->assertOk();
        $this->put(route('admin.inventory.update', 1), ['so_luong_ton' => 10])->assertForbidden();
        $this->get(route('admin.chat.index'))->assertForbidden();
    }

    public function test_admin_can_assign_a_sensitive_permission_and_it_is_audited(): void
    {
        $admin = $this->createUserWithRole('admin');
        $accountant = $this->createUserWithRole('ke_toan');
        $this->actingAs($admin)->withSession(['admin_2fa_verified_user' => $admin->id]);

        $this->patch(route('admin.users.permissions.update', $accountant), [
            'permissions' => ['payments.confirm'],
        ])->assertRedirect(route('admin.users.index'));

        $this->assertEqualsCanonicalizing(
            ['payments.view', 'payments.confirm'],
            $accountant->fresh()->quyen_han,
        );
        $this->assertDatabaseHas('admin_action_logs', [
            'id_nguoi_thuc_hien' => $admin->id,
            'hanh_dong' => 'user.permissions.updated',
        ]);
    }

    public function test_inventory_edit_permission_does_not_allow_receiving_stock_without_import_permission(): void
    {
        $admin = $this->createUserWithRole('admin');
        $warehouseStaff = $this->createUserWithRole('nhan_vien_kho');
        $this->actingAs($admin)->withSession(['admin_2fa_verified_user' => $admin->id]);

        $this->patch(route('admin.users.permissions.update', $warehouseStaff), [
            'permissions' => ['inventory.update'],
        ])->assertRedirect(route('admin.users.index'));

        $warehouseStaff = $warehouseStaff->fresh();
        $this->assertTrue($warehouseStaff->hasPermission('inventory.update'));
        $this->assertFalse($warehouseStaff->hasPermission('inventory.import'));

        $this->actingAs($warehouseStaff)->withSession([
            'admin_2fa_verified_user' => $warehouseStaff->id,
        ])->put(route('admin.inventory.update', 1), [
            'so_luong_nhap' => 5,
        ])->assertForbidden();
    }

    public function test_staff_cannot_open_action_logs_or_manage_other_staff_permissions(): void
    {
        $staff = $this->createUserWithRole('cskh');
        $target = $this->createUserWithRole('ke_toan');
        $this->actingAs($staff)->withSession(['admin_2fa_verified_user' => $staff->id]);

        $this->get(route('admin.action-logs.index'))->assertForbidden();
        $this->patch(route('admin.users.permissions.update', $target), [
            'permissions' => ['inventory.view'],
        ])->assertForbidden();
    }

    private function createUserWithRole(string $roleName): NguoiDung
    {
        $user = NguoiDung::factory()->createOne();
        $role = VaiTro::firstOrCreate(['ten_vai_tro' => $roleName]);
        $user->vaiTro()->attach($role);

        return $user;
    }
}
