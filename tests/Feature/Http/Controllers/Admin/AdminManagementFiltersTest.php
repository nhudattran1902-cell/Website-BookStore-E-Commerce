<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\DonHang;
use App\Models\NguoiDung;
use App\Models\Sach;
use App\Models\TacGia;
use App\Models\TheLoai;
use App\Models\VaiTro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminManagementFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_filters_payment_status_dates_and_total_sorting(): void
    {
        $admin = $this->createAdmin();
        $matchingOrder = $this->createOrder('VNPay', 'dang_giao', 90000, '2026-09-15 10:00:00');
        $lowerOrder = $this->createOrder('VNPay', 'dang_giao', 45000, '2026-09-12 10:00:00');
        $this->createOrder('COD', 'dang_giao', 120000, '2026-09-15 10:00:00');
        $this->createOrder('VNPay', 'hoan_thanh', 160000, '2026-09-15 10:00:00');

        $response = $this->actingAsAdminWithTwoFactor($admin)->get(route('admin.orders.index', [
            'sort_total' => 'desc',
            'phuong_thuc_thanh_toan' => 'VNPay',
            'trang_thai' => 'dang_giao',
            'date_from' => '2026-09-10',
            'date_to' => '2026-09-20',
        ]));

        $response->assertOk()
            ->assertViewHas('orders', function ($orders) use ($matchingOrder, $lowerOrder): bool {
                return $orders->total() === 2
                    && $orders->first()->is($matchingOrder)
                    && $orders->getCollection()->contains(fn (DonHang $order): bool => $order->is($lowerOrder));
            });
    }

    public function test_order_filter_supports_vietqr_bank_transfers(): void
    {
        $admin = $this->createAdmin();
        $bankTransferOrder = $this->createOrder('BankTransfer', 'dang_giao', 90000, '2026-09-15 10:00:00');
        $this->createOrder('COD', 'dang_giao', 45000, '2026-09-15 10:00:00');

        $response = $this->actingAsAdminWithTwoFactor($admin)->get(route('admin.orders.index', [
            'phuong_thuc_thanh_toan' => 'BankTransfer',
        ]));

        $response->assertViewHas('orders', function ($orders) use ($bankTransferOrder): bool {
            return $orders->total() === 1
                && $orders->first()->is($bankTransferOrder);
        })->assertSee('Chuyển khoản VietQR');
    }

    public function test_book_filters_match_title_category_price_stock_and_business_status(): void
    {
        $admin = $this->createAdmin();
        $category = TheLoai::create([
            'ten_the_loai' => 'Văn học thử nghiệm',
            'duong_dan_tinh' => 'van-hoc-thu-nghiem',
        ]);
        $matchingBook = $this->createBook($category, 'Tiểu thuyết mùa thu', 85000, 3, true);
        $this->createBook($category, 'Tiểu thuyết mùa đông', 85000, 8, true);
        $this->createBook($category, 'Tiểu thuyết mùa hạ', 85000, 2, false);

        $response = $this->actingAsAdminWithTwoFactor($admin)->get(route('admin.books.index', [
            'search' => 'mùa thu',
            'the_loai' => $category->id,
            'gia_tu' => 80000,
            'gia_den' => 90000,
            'ton_kho' => 'low',
            'trang_thai' => 'active',
        ]));

        $response->assertOk()
            ->assertViewHas('books', function ($books) use ($matchingBook): bool {
                return $books->total() === 1 && $books->first()->is($matchingBook);
            });
    }

    public function test_rejects_unapproved_filter_values(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAsAdminWithTwoFactor($admin)->get(route('admin.books.index', [
            'sort_gia' => 'gia_ban desc; drop table sach',
            'ton_kho' => 'all; delete',
        ]));

        $response->assertSessionHasErrors(['sort_gia', 'ton_kho']);
    }

    public function test_replacing_a_book_cover_stores_new_image_and_deletes_old_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $category = TheLoai::create([
            'ten_the_loai' => 'Trinh thám thử nghiệm',
            'duong_dan_tinh' => 'trinh-tham-thu-nghiem',
        ]);
        $book = $this->createBook($category, 'Bí ẩn trên kệ sách', 100000, 10, true);
        $oldCover = 'books/old-cover.png';
        Storage::disk('public')->put($oldCover, 'old image');
        $book->update(['anh_bia' => $oldCover]);

        $response = $this->actingAsAdminWithTwoFactor($admin)->put(route('admin.books.update', $book->id), [
            'tieu_de' => $book->tieu_de,
            'id_the_loai' => $category->id,
            'gia_ban' => $book->gia_ban,
            'so_luong_ton' => 10,
            'anh_bia' => $this->fakePng('new-cover.png'),
        ]);

        $response->assertRedirect(route('admin.books.index'));
        $book->refresh();
        $this->assertNotSame($oldCover, $book->anh_bia);
        $this->assertStringStartsWith('books/', $book->anh_bia);
        Storage::disk('public')->assertExists($book->anh_bia);
        Storage::disk('public')->assertMissing($oldCover);
    }

    public function test_replacing_an_author_image_stores_new_image_and_deletes_old_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $author = TacGia::create([
            'ten_tac_gia' => 'Tác giả thử nghiệm',
            'anh_dai_dien' => 'authors/old-avatar.png',
        ]);
        $oldImage = $author->anh_dai_dien;
        Storage::disk('public')->put($oldImage, 'old image');

        $response = $this->actingAsAdminWithTwoFactor($admin)->put(route('admin.authors.update', $author->id), [
            'ten_tac_gia' => $author->ten_tac_gia,
            'tieu_su' => 'Tiểu sử thử nghiệm',
            'anh_dai_dien' => $this->fakePng('new-avatar.png'),
        ]);

        $response->assertRedirect(route('admin.authors.index'));
        $author->refresh();
        $this->assertNotSame($oldImage, $author->anh_dai_dien);
        $this->assertStringStartsWith('authors/', $author->anh_dai_dien);
        Storage::disk('public')->assertExists($author->anh_dai_dien);
        Storage::disk('public')->assertMissing($oldImage);
    }

    public function test_filter_pages_require_an_admin_role(): void
    {
        /** @var NguoiDung $customer */
        $customer = NguoiDung::factory()->createOne(['ho_ten' => 'Khách hàng']);

        $this->actingAs($customer)
            ->get(route('admin.orders.index'))
            ->assertForbidden();
    }

    private function createAdmin(): NguoiDung
    {
        $admin = NguoiDung::factory()->createOne(['ho_ten' => 'Quản trị viên']);
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

        return $this->actingAs($admin)->withSession([
            'admin_2fa_verified_user' => $admin->id,
        ]);
    }

    private function createBook(TheLoai $category, string $title, int $price, int $stock, bool $active): Sach
    {
        $book = Sach::create([
            'tieu_de' => $title,
            'duong_dan_tinh' => str($title)->slug()->toString(),
            'id_the_loai' => $category->id,
            'gia_ban' => $price,
            'dang_hoat_dong' => $active,
        ]);
        $book->khoHang()->create([
            'so_luong_ton' => $stock,
            'so_luong_dat_truoc' => 0,
        ]);

        return $book;
    }

    private function createOrder(string $method, string $status, int $total, string $createdAt): DonHang
    {
        /** @var NguoiDung $customer */
        $customer = NguoiDung::factory()->createOne(['ho_ten' => 'Khách hàng']);
        $order = DonHang::create([
            'ma_don_hang' => 'TEST-'.str()->random(10),
            'id_nguoi_dung' => $customer->id,
            'tong_tien' => $total,
            'so_tien_giam_gia' => 0,
            'thanh_tien' => $total,
            'trang_thai' => $status,
            'dia_chi_giao_hang' => 'Địa chỉ thử nghiệm',
        ]);
        $order->forceFill(['ngay_tao' => $createdAt])->save();
        $order->thanhToan()->create([
            'phuong_thuc_thanh_toan' => $method,
            'so_tien' => $total,
        ]);

        return $order;
    }

    private function fakePng(string $filename): UploadedFile
    {
        $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/w4kAAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($filename, $contents);
    }
}
