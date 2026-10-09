<?php

namespace Tests\Feature;

use App\Models\DonHang;
use App\Models\GioHang;
use App\Models\KhoHang;
use App\Models\MaGiamGia;
use App\Models\NguoiDung;
use App\Models\Sach;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_discount_is_applied_to_checkout_and_usage_is_recorded(): void
    {
        $customer = NguoiDung::factory()->createOne();
        $book = $this->createBookWithStock(5, 100000);
        $cart = GioHang::create(['id_nguoi_dung' => $customer->id]);
        $cart->chiTietGioHang()->create(['id_sach' => $book->id, 'so_luong' => 1]);
        $coupon = MaGiamGia::create([
            'ma_code' => 'SACH20',
            'loai_giam' => 'phan_tram',
            'gia_tri' => 20,
            'gia_tri_toi_da' => 15000,
            'don_toi_thieu' => 50000,
            'gioi_han_luot' => 1,
            'dang_hoat_dong' => true,
        ]);

        $this->actingAs($customer)
            ->post(route('checkout.discount.apply'), ['ma_code' => 'sach20'])
            ->assertRedirect(route('checkout.index'));

        $this->actingAs($customer)->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Giảm giá (SACH20)')
            ->assertSee('−15.000 đ');

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
        $this->assertSame($coupon->id, $order->id_ma_giam_gia);
        $this->assertSame(100000, (int) $order->tong_tien);
        $this->assertSame(15000, (int) $order->so_tien_giam_gia);
        $this->assertSame(85000, (int) $order->thanh_tien);
        $this->assertSame(85000, (int) $order->thanhToan->so_tien);
        $this->assertSame(1, $coupon->fresh()->da_su_dung);
        $this->assertFalse(session()->has('checkout.discount_code'));
    }

    public function test_discount_code_is_rejected_when_order_is_below_minimum(): void
    {
        $customer = NguoiDung::factory()->createOne();
        $book = $this->createBookWithStock(5, 40000);
        $cart = GioHang::create(['id_nguoi_dung' => $customer->id]);
        $cart->chiTietGioHang()->create(['id_sach' => $book->id, 'so_luong' => 1]);
        MaGiamGia::create([
            'ma_code' => 'MINIMUM',
            'loai_giam' => 'so_tien_co_dinh',
            'gia_tri' => 10000,
            'don_toi_thieu' => 50000,
            'dang_hoat_dong' => true,
        ]);

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.discount.apply'), ['ma_code' => 'MINIMUM'])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('discount_code');

        $this->assertFalse(session()->has('checkout.discount_code'));
    }

    public function test_checkout_lists_only_vouchers_available_for_the_customer_and_cart(): void
    {
        $customer = NguoiDung::factory()->createOne();
        $book = $this->createBookWithStock(5, 100000);
        $cart = GioHang::create(['id_nguoi_dung' => $customer->id]);
        $cart->chiTietGioHang()->create(['id_sach' => $book->id, 'so_luong' => 1]);

        MaGiamGia::create([
            'ma_code' => 'AVAILABLE',
            'loai_giam' => 'phan_tram',
            'gia_tri' => 10,
            'don_toi_thieu' => 50000,
            'gioi_han_luot' => 5,
            'dang_hoat_dong' => true,
        ]);
        $usedCoupon = MaGiamGia::create([
            'ma_code' => 'ALREADYUSED',
            'loai_giam' => 'so_tien_co_dinh',
            'gia_tri' => 10000,
            'don_toi_thieu' => 0,
            'dang_hoat_dong' => true,
        ]);
        DonHang::create([
            'ma_don_hang' => 'USED-VOUCHER-ORDER',
            'id_nguoi_dung' => $customer->id,
            'id_ma_giam_gia' => $usedCoupon->id,
            'tong_tien' => 100000,
            'so_tien_giam_gia' => 10000,
            'thanh_tien' => 90000,
            'trang_thai' => 'da_huy',
        ]);
        MaGiamGia::create([
            'ma_code' => 'MINIMUMNOTMET',
            'loai_giam' => 'so_tien_co_dinh',
            'gia_tri' => 10000,
            'don_toi_thieu' => 200000,
            'dang_hoat_dong' => true,
        ]);
        MaGiamGia::create([
            'ma_code' => 'SOLDOUT',
            'loai_giam' => 'so_tien_co_dinh',
            'gia_tri' => 10000,
            'gioi_han_luot' => 2,
            'da_su_dung' => 2,
            'dang_hoat_dong' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('AVAILABLE')
            ->assertSee('Tiết kiệm 10.000 đ')
            ->assertDontSee('ALREADYUSED')
            ->assertDontSee('MINIMUMNOTMET')
            ->assertDontSee('SOLDOUT');
    }

    public function test_customer_cannot_redeem_the_same_voucher_again_after_a_cancelled_order(): void
    {
        $customer = NguoiDung::factory()->createOne();
        $firstBook = $this->createBookWithStock(5, 100000);
        $coupon = MaGiamGia::create([
            'ma_code' => 'ONCEONLY',
            'loai_giam' => 'so_tien_co_dinh',
            'gia_tri' => 10000,
            'don_toi_thieu' => 0,
            'dang_hoat_dong' => true,
        ]);
        DonHang::create([
            'ma_don_hang' => 'CANCELLED-VOUCHER-ORDER',
            'id_nguoi_dung' => $customer->id,
            'id_ma_giam_gia' => $coupon->id,
            'tong_tien' => 100000,
            'so_tien_giam_gia' => 10000,
            'thanh_tien' => 90000,
            'trang_thai' => 'da_huy',
        ]);
        $cart = GioHang::create(['id_nguoi_dung' => $customer->id]);
        $cart->chiTietGioHang()->create(['id_sach' => $firstBook->id, 'so_luong' => 1]);

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.discount.apply'), ['ma_code' => 'ONCEONLY'])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('discount_code');
    }

    private function createBookWithStock(int $stock, int $price): Sach
    {
        $book = Sach::create([
            'tieu_de' => 'Sách kiểm thử giảm giá',
            'duong_dan_tinh' => 'sach-kiem-thu-giam-gia',
            'gia_ban' => $price,
            'dang_hoat_dong' => true,
        ]);

        KhoHang::create([
            'id_sach' => $book->id,
            'so_luong_ton' => $stock,
            'so_luong_dat_truoc' => 0,
        ]);

        return $book;
    }
}
