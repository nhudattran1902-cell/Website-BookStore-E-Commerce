<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\NguoiDung;
use App\Models\Sach;
use App\Models\TheLoai;
use App\Models\VaiTro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_book_edition_and_physical_details(): void
    {
        $admin = $this->createAdmin();
        $category = TheLoai::create([
            'ten_the_loai' => 'Sách chuyên ngành',
            'duong_dan_tinh' => 'sach-chuyen-nganh',
        ]);

        $adminSession = $this->actingAsAdminWithTwoFactor($admin);
        $adminSession->get(route('admin.books.create'))
            ->assertOk()
            ->assertSee('Thông số ấn bản')
            ->assertSee('name="khoi_luong_gram"', false);

        $adminSession->post(route('admin.books.store'), [
            'tieu_de' => 'Sách có thông số đầy đủ',
            'id_the_loai' => $category->id,
            'gia_ban' => 120000,
            'so_trang' => 320,
            'loai_bia' => 'bia_cung',
            'khoi_luong_gram' => 540,
            'chieu_rong_mm' => 150,
            'chieu_cao_mm' => 230,
            'do_day_mm' => 28.5,
            'ngon_ngu' => 'Tiếng Việt',
            'lan_tai_ban' => 3,
            'nha_cung_cap' => 'Công ty Sách Minh Tâm',
            'so_luong_ton' => 0,
            'dang_hoat_dong' => 1,
        ])
            ->assertRedirect(route('admin.books.index'));

        $book = Sach::query()->where('tieu_de', 'Sách có thông số đầy đủ')->firstOrFail();

        $this->assertSame(320, $book->so_trang);
        $this->assertSame('bia_cung', $book->loai_bia);
        $this->assertSame(540, $book->khoi_luong_gram);
        $this->assertSame(150.0, (float) $book->chieu_rong_mm);
        $this->assertSame(230.0, (float) $book->chieu_cao_mm);
        $this->assertSame(28.5, (float) $book->do_day_mm);
        $this->assertSame('Tiếng Việt', $book->ngon_ngu);
        $this->assertSame(3, $book->lan_tai_ban);
        $this->assertSame('Công ty Sách Minh Tâm', $book->nha_cung_cap);

        $this->get(route('books.show', $book->id))
            ->assertOk()
            ->assertSee('Thông số sách')
            ->assertSee('Bìa cứng')
            ->assertSee('Tiếng Việt')
            ->assertSee('Công ty Sách Minh Tâm');
    }

    public function test_admin_cannot_save_invalid_book_cover_type_or_dimensions(): void
    {
        $admin = $this->createAdmin();
        $category = TheLoai::create([
            'ten_the_loai' => 'Sách kỹ thuật',
            'duong_dan_tinh' => 'sach-ky-thuat',
        ]);

        $this->actingAsAdminWithTwoFactor($admin)
            ->post(route('admin.books.store'), [
                'tieu_de' => 'Thông số không hợp lệ',
                'id_the_loai' => $category->id,
                'gia_ban' => 100000,
                'loai_bia' => 'bia-khong-hop-le',
                'chieu_rong_mm' => 0,
            ])
            ->assertSessionHasErrors(['loai_bia', 'chieu_rong_mm']);

        $this->assertDatabaseMissing('sach', ['tieu_de' => 'Thông số không hợp lệ']);
    }

    public function test_admin_can_update_existing_book_metadata(): void
    {
        $admin = $this->createAdmin();
        $category = TheLoai::create([
            'ten_the_loai' => 'Sách thiếu nhi',
            'duong_dan_tinh' => 'sach-thieu-nhi',
        ]);
        $book = Sach::create([
            'tieu_de' => 'Sách đang có',
            'duong_dan_tinh' => 'sach-dang-co',
            'id_the_loai' => $category->id,
            'gia_ban' => 70000,
            'dang_hoat_dong' => true,
        ]);

        $adminSession = $this->actingAsAdminWithTwoFactor($admin);
        $adminSession->get(route('admin.books.edit', $book->id))
            ->assertOk()
            ->assertSee('Thông số ấn bản')
            ->assertSee('name="lan_tai_ban"', false);

        $adminSession->put(route('admin.books.update', $book->id), [
            'tieu_de' => $book->tieu_de,
            'id_the_loai' => $category->id,
            'gia_ban' => 70000,
            'so_trang' => 96,
            'loai_bia' => 'bia_mem',
            'khoi_luong_gram' => 180,
            'chieu_rong_mm' => 128,
            'chieu_cao_mm' => 198,
            'do_day_mm' => 9,
            'ngon_ngu' => 'Tiếng Anh',
            'lan_tai_ban' => 2,
            'nha_cung_cap' => 'Nhà sách Ánh Dương',
        ])
            ->assertRedirect(route('admin.books.index'));

        $this->assertDatabaseHas('sach', [
            'id' => $book->id,
            'so_trang' => 96,
            'loai_bia' => 'bia_mem',
            'khoi_luong_gram' => 180,
            'ngon_ngu' => 'Tiếng Anh',
            'lan_tai_ban' => 2,
            'nha_cung_cap' => 'Nhà sách Ánh Dương',
        ]);
    }

    private function createAdmin(): NguoiDung
    {
        $admin = NguoiDung::factory()->createOne();
        $role = VaiTro::create(['ten_vai_tro' => 'admin']);
        $admin->vaiTro()->attach($role);

        return $admin;
    }

    private function actingAsAdminWithTwoFactor(NguoiDung $admin): self
    {
        $admin->forceFill([
            'admin_totp_secret' => 'JBSWY3DPEHPK3PXP',
            'admin_totp_confirmed_at' => now(),
        ])->save();

        return $this->actingAs($admin)->withSession([
            'admin_2fa_verified_user' => $admin->id,
        ]);
    }
}
