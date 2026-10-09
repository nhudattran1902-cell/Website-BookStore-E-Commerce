<?php

namespace Tests\Feature;

use App\Models\ChiTietDonHang;
use App\Models\DonHang;
use App\Models\GioHang;
use App\Models\KhoHang;
use App\Models\NguoiDung;
use App\Models\Sach;
use App\Models\VaiTro;
use App\Services\OrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_reserves_stock_and_saves_recipient_fields(): void
    {
        /** @var NguoiDung $customer */
        $customer = NguoiDung::factory()->createOne();
        $book = $this->createBookWithStock(5);
        $cart = GioHang::create(['id_nguoi_dung' => $customer->id]);
        $cart->chiTietGioHang()->create(['id_sach' => $book->id, 'so_luong' => 2]);

        $response = $this->actingAs($customer)->post(route('checkout.process'), [
            'ten_nguoi_nhan' => 'Nguyen Van A',
            'sdt_nguoi_nhan' => '0900000000',
            'so_nha_duong' => '1 Duong Test',
            'province_code' => '01',
            'ward_code' => '00004',
            'phuong_thuc_thanh_toan' => 'COD',
        ]);

        $order = DonHang::query()->firstOrFail();
        $response->assertRedirect(route('checkout.success', $order->ma_don_hang));
        $this->assertSame('Nguyen Van A', $order->ten_nguoi_nhan);
        $this->assertSame('0900000000', $order->sdt_nguoi_nhan);
        $this->assertSame('1 Duong Test, Ba Đình, Hà Nội', $order->dia_chi_nhan);
        $this->assertTrue($order->da_giu_ton);
        $this->assertSame(5, $this->stockFor($book)->so_luong_ton);
        $this->assertSame(2, $this->stockFor($book)->so_luong_dat_truoc);
        $this->assertDatabaseHas('lich_su_don_hang', [
            'id_don_hang' => $order->id,
            'trang_thai_moi' => 'cho_xu_ly',
        ]);
        $this->assertDatabaseMissing('chi_tiet_gio_hang', ['id_gio_hang' => $cart->id]);
    }

    public function test_checkout_rolls_back_when_stock_is_reserved_by_another_order(): void
    {
        /** @var NguoiDung $customer */
        $customer = NguoiDung::factory()->createOne();
        $book = $this->createBookWithStock(1);
        $book->khoHang()->update(['so_luong_dat_truoc' => 1]);
        $cart = GioHang::create(['id_nguoi_dung' => $customer->id]);
        $cart->chiTietGioHang()->create(['id_sach' => $book->id, 'so_luong' => 1]);

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.process'), [
                'ten_nguoi_nhan' => 'Nguyen Van B',
                'sdt_nguoi_nhan' => '0900000001',
                'so_nha_duong' => '2 Duong Test',
                'province_code' => '01',
                'ward_code' => '00004',
                'phuong_thuc_thanh_toan' => 'COD',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('stock');

        $this->assertDatabaseCount('don_hang', 0);
        $this->assertDatabaseHas('kho_hang', ['id_sach' => $book->id, 'so_luong_dat_truoc' => 1]);
    }

    public function test_customer_cancel_releases_reservation_without_changing_physical_stock(): void
    {
        /** @var NguoiDung $customer */
        $customer = NguoiDung::factory()->createOne();
        [$order, $book] = $this->createReservedOrder($customer, 'COD');

        $this->actingAs($customer)
            ->from(route('customer.orders.show', $order->id))
            ->patch(route('customer.orders.cancel', $order->id))
            ->assertRedirect(route('customer.orders.show', $order->id));

        $this->assertSame('da_huy', $order->fresh()->trang_thai);
        $this->assertSame(10, $this->stockFor($book)->so_luong_ton);
        $this->assertSame(0, $this->stockFor($book)->so_luong_dat_truoc);
        $this->assertDatabaseHas('lich_su_don_hang', [
            'id_don_hang' => $order->id,
            'trang_thai_cu' => 'cho_xu_ly',
            'trang_thai_moi' => 'da_huy',
        ]);
    }

    public function test_customer_can_edit_recipient_until_shipment_but_not_after(): void
    {
        /** @var NguoiDung $customer */
        $customer = NguoiDung::factory()->createOne();
        [$order] = $this->createReservedOrder($customer, 'COD');

        $this->actingAs($customer)
            ->patch(route('customer.orders.updateAddress', $order->id), [
                'ten_nguoi_nhan' => 'Nguoi nhan moi',
                'sdt_nguoi_nhan' => '0912345678',
                'dia_chi_nhan' => 'Dia chi moi',
            ])
            ->assertSessionHas('success');

        $this->assertSame('Nguoi nhan moi', $order->fresh()->ten_nguoi_nhan);
        $this->assertSame('0912345678', $order->fresh()->sdt_nguoi_nhan);
        $this->assertSame('Dia chi moi', $order->fresh()->dia_chi_nhan);

        app(OrderWorkflowService::class)->transition($order->id, 'dang_xu_ly', null);
        app(OrderWorkflowService::class)->transition($order->id, 'dang_giao', null);

        $this->patch(route('customer.orders.updateAddress', $order->id), [
            'ten_nguoi_nhan' => 'Khong duoc sua',
            'sdt_nguoi_nhan' => '0900000000',
            'dia_chi_nhan' => 'Dia chi khac',
        ])->assertSessionHas('error');

        $this->assertSame('Nguoi nhan moi', $order->fresh()->ten_nguoi_nhan);
    }

    public function test_customer_cannot_view_another_users_order(): void
    {
        /** @var NguoiDung $owner */
        $owner = NguoiDung::factory()->createOne();
        /** @var NguoiDung $otherCustomer */
        $otherCustomer = NguoiDung::factory()->createOne();
        [$order] = $this->createReservedOrder($owner, 'COD');

        $this->actingAs($otherCustomer)
            ->get(route('customer.orders.show', $order->id))
            ->assertNotFound();
    }

    public function test_admin_must_follow_order_progression_and_commit_reserved_stock_once(): void
    {
        /** @var NguoiDung $customer */
        $customer = NguoiDung::factory()->createOne();
        [$order, $book] = $this->createReservedOrder($customer, 'COD');
        $admin = $this->createAdmin();
        $this->authenticateAdminWithTwoFactor($admin);

        $this->put(route('admin.orders.updateStatus', $order->id), ['trang_thai' => 'dang_giao'])
            ->assertSessionHas('error');
        $this->assertSame(10, $this->stockFor($book)->so_luong_ton);
        $this->assertSame(2, $this->stockFor($book)->so_luong_dat_truoc);

        $this->put(route('admin.orders.updateStatus', $order->id), ['trang_thai' => 'dang_xu_ly'])
            ->assertSessionHas('success');
        $this->assertSame(8, $this->stockFor($book)->so_luong_ton);
        $this->assertSame(0, $this->stockFor($book)->so_luong_dat_truoc);

        $this->put(route('admin.orders.updateStatus', $order->id), ['trang_thai' => 'dang_giao'])
            ->assertSessionHas('success');
        $this->put(route('admin.orders.updateStatus', $order->id), ['trang_thai' => 'hoan_thanh'])
            ->assertSessionHas('success');

        $this->assertSame('hoan_thanh', $order->fresh()->trang_thai);
        $this->assertSame(8, $this->stockFor($book)->so_luong_ton);
        $this->assertDatabaseCount('lich_su_don_hang', 3);
        $this->assertDatabaseHas('thanh_toan', [
            'id_don_hang' => $order->id,
            'trang_thai' => 'da_thanh_toan',
        ]);
        $this->assertNotNull($order->fresh()->thanhToan->ngay_thanh_toan);
    }

    private function createBookWithStock(int $quantity): Sach
    {
        $book = Sach::create([
            'tieu_de' => 'Sach test '.$quantity,
            'duong_dan_tinh' => 'sach-test-'.$quantity,
            'gia_ban' => 25000,
            'dang_hoat_dong' => true,
        ]);
        $book->khoHang()->create([
            'so_luong_ton' => $quantity,
            'so_luong_dat_truoc' => 0,
        ]);

        return $book;
    }

    private function stockFor(Sach $book): KhoHang
    {
        /** @var KhoHang $stock */
        $stock = $book->khoHang()->firstOrFail();

        return $stock;
    }

    /** @return array{DonHang, Sach} */
    private function createReservedOrder(NguoiDung $customer, string $paymentMethod): array
    {
        $book = $this->createBookWithStock(10);
        $book->khoHang()->update(['so_luong_dat_truoc' => 2]);
        $order = DonHang::create([
            'ma_don_hang' => 'ORD-'.str()->random(10),
            'id_nguoi_dung' => $customer->id,
            'ten_nguoi_nhan' => 'Nguoi nhan test',
            'sdt_nguoi_nhan' => '0900000000',
            'dia_chi_nhan' => 'Dia chi test',
            'tong_tien' => 50000,
            'so_tien_giam_gia' => 0,
            'thanh_tien' => 50000,
            'trang_thai' => 'cho_xu_ly',
            'da_giu_ton' => true,
        ]);
        ChiTietDonHang::create([
            'id_don_hang' => $order->id,
            'id_sach' => $book->id,
            'don_gia' => 25000,
            'so_luong' => 2,
            'thanh_tien' => 50000,
        ]);
        $order->thanhToan()->create([
            'phuong_thuc_thanh_toan' => $paymentMethod,
            'so_tien' => 50000,
            'trang_thai' => 'cho_thanh_toan',
        ]);

        return [$order, $book];
    }

    private function createAdmin(): NguoiDung
    {
        $admin = NguoiDung::factory()->createOne();
        $role = VaiTro::create(['ten_vai_tro' => 'admin']);
        $admin->vaiTro()->attach($role);

        return $admin;
    }

    private function authenticateAdminWithTwoFactor(NguoiDung $admin): void
    {
        $admin->forceFill([
            'admin_totp_secret' => 'JBSWY3DPEHPK3PXP',
            'admin_totp_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)->withSession(['admin_2fa_verified_user' => $admin->id]);
    }
}
