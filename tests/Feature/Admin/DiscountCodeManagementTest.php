<?php

namespace Tests\Feature\Admin;

use App\Models\NguoiDung;
use App\Models\VaiTro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountCodeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_percentage_discount_code(): void
    {
        $admin = $this->createAdmin();

        $this->actingAsAdminWithTwoFactor($admin)
            ->get(route('admin.discount-codes.create'))
            ->assertOk()
            ->assertSee('Tạo mã giảm giá');

        $response = $this->post(route('admin.discount-codes.store'), [
            'ma_code' => '  sach20 ',
            'loai_giam' => 'phan_tram',
            'gia_tri' => 20,
            'gia_tri_toi_da' => 50000,
            'don_toi_thieu' => 100000,
            'gioi_han_luot' => 25,
            'dang_hoat_dong' => 1,
        ]);

        $response->assertRedirect(route('admin.discount-codes.index'));
        $this->assertDatabaseHas('ma_giam_gia', [
            'ma_code' => 'SACH20',
            'loai_giam' => 'phan_tram',
            'gia_tri' => 20,
            'gia_tri_toi_da' => 50000,
            'don_toi_thieu' => 100000,
            'gioi_han_luot' => 25,
            'da_su_dung' => 0,
            'dang_hoat_dong' => true,
        ]);

        $this->get(route('admin.discount-codes.index'))
            ->assertOk()
            ->assertSee('SACH20');
    }

    public function test_admin_discount_page_is_forbidden_to_customers(): void
    {
        $customer = NguoiDung::factory()->createOne();

        $this->actingAs($customer)
            ->get(route('admin.discount-codes.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_create_percentage_discount_over_one_hundred(): void
    {
        $admin = $this->createAdmin();

        $this->actingAsAdminWithTwoFactor($admin)
            ->from(route('admin.discount-codes.create'))
            ->post(route('admin.discount-codes.store'), [
                'ma_code' => 'TOOMUCH',
                'loai_giam' => 'phan_tram',
                'gia_tri' => 101,
                'don_toi_thieu' => 0,
                'dang_hoat_dong' => 1,
            ])
            ->assertRedirect(route('admin.discount-codes.create'))
            ->assertSessionHasErrors('gia_tri');

        $this->assertDatabaseMissing('ma_giam_gia', ['ma_code' => 'TOOMUCH']);
    }

    private function createAdmin(): NguoiDung
    {
        $admin = NguoiDung::factory()->createOne();
        $adminRole = VaiTro::create(['ten_vai_tro' => 'admin']);
        $admin->vaiTro()->attach($adminRole);

        return $admin;
    }

    private function actingAsAdminWithTwoFactor(NguoiDung $admin): self
    {
        $admin->forceFill([
            'admin_totp_secret' => 'JBSWY3DPEHPK3PXP',
            'admin_totp_confirmed_at' => now(),
        ])->save();

        return $this->actingAs($admin)->withSession(['admin_2fa_verified_user' => $admin->id]);
    }
}
