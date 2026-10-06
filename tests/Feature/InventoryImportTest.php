<?php

namespace Tests\Feature;

use App\Models\KhoHang;
use App\Models\NguoiDung;
use App\Models\NhaXuatBan;
use App\Models\PhieuNhapKho;
use App\Models\Sach;
use App\Models\VaiTro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_import_receipt_and_update_stock_and_weighted_cost(): void
    {
        $admin = $this->createAdmin();
        $book = Sach::create([
            'tieu_de' => 'Sach nhap kho',
            'duong_dan_tinh' => 'sach-nhap-kho',
            'gia_ban' => 50000,
            'gia_von' => 10000,
            'dang_hoat_dong' => true,
        ]);
        $book->khoHang()->create(['so_luong_ton' => 5, 'so_luong_dat_truoc' => 1]);
        $publisher = NhaXuatBan::create(['ten_nxb' => 'Nha xuat ban test']);

        $this->authenticateAdmin($admin);
        $this->get(route('admin.inventory.imports.index'))
            ->assertOk()
            ->assertViewIs('admin.inventory.import_index');
        $this->get(route('admin.inventory.imports.create'))
            ->assertOk()
            ->assertViewIs('admin.inventory.import_create');

        $this->post(route('admin.inventory.imports.store'), [
            'id_nha_xuat_ban' => $publisher->id,
            'ghi_chu' => 'Nhap dot test',
            'items' => [
                ['id_sach' => $book->id, 'so_luong' => 3, 'don_gia_nhap' => 20000],
            ],
        ])->assertRedirect(route('admin.inventory.imports.index'));

        $receipt = PhieuNhapKho::query()->firstOrFail();
        $this->assertDatabaseHas('chi_tiet_phieu_nhap', [
            'id_phieu_nhap' => $receipt->id,
            'id_sach' => $book->id,
            'so_luong' => 3,
            'don_gia_nhap' => 20000,
            'thanh_tien' => 60000,
        ]);
        $this->assertSame(60000.0, (float) $receipt->tong_tien);
        $this->assertSame(8, $this->stockFor($book)->so_luong_ton);
        $this->assertSame(1, $this->stockFor($book)->so_luong_dat_truoc);
        $this->assertSame(13750.0, (float) $book->fresh()->gia_von);
    }

    private function createAdmin(): NguoiDung
    {
        $admin = NguoiDung::factory()->createOne();
        $role = VaiTro::create(['ten_vai_tro' => 'admin']);
        $admin->vaiTro()->attach($role);

        return $admin;
    }

    private function authenticateAdmin(NguoiDung $admin): void
    {
        $admin->forceFill([
            'admin_totp_secret' => 'JBSWY3DPEHPK3PXP',
            'admin_totp_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)->withSession(['admin_2fa_verified_user' => $admin->id]);
    }

    private function stockFor(Sach $book): KhoHang
    {
        /** @var KhoHang $stock */
        $stock = $book->khoHang()->firstOrFail();

        return $stock;
    }
}
