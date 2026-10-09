<?php

namespace Tests\Feature;

use App\Models\GioHang;
use App\Models\KhoHang;
use App\Models\NguoiDung;
use App\Models\Sach;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_location_routes_return_current_provinces_and_wards(): void
    {
        $customer = NguoiDung::factory()->createOne();

        $this->actingAs($customer)
            ->get(route('checkout.locations.provinces'))
            ->assertOk()
            ->assertJsonFragment(['id' => '01', 'name' => 'Hà Nội'])
            ->assertJsonCount(34, 'provinces');

        $this->actingAs($customer)
            ->get(route('checkout.locations.wards', '01'))
            ->assertOk()
            ->assertJsonFragment(['id' => '00004', 'name' => 'Ba Đình']);
    }

    public function test_checkout_page_shows_dependent_province_and_ward_fields(): void
    {
        $customer = NguoiDung::factory()->createOne();
        $this->createCartWithBook($customer);

        $this->actingAs($customer)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Tỉnh/Thành phố')
            ->assertSee('Phường/Xã')
            ->assertSee('Số nhà, tên đường / thôn, ấp')
            ->assertSee('Chọn Tỉnh/Thành phố trước');
    }

    public function test_checkout_rejects_ward_that_does_not_belong_to_selected_province(): void
    {
        $customer = NguoiDung::factory()->createOne();
        $this->createCartWithBook($customer);

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.process'), [
                'ten_nguoi_nhan' => 'Khach Hang',
                'sdt_nguoi_nhan' => '0900000000',
                'so_nha_duong' => '12 Nguyen Hue',
                'province_code' => '79',
                'ward_code' => '00004',
                'phuong_thuc_thanh_toan' => 'COD',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('ward_code');

        $this->assertDatabaseCount('don_hang', 0);
    }

    public function test_checkout_requires_all_structured_address_fields(): void
    {
        $customer = NguoiDung::factory()->createOne();
        $this->createCartWithBook($customer);

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.process'), [
                'ten_nguoi_nhan' => 'Khach Hang',
                'sdt_nguoi_nhan' => '0900000000',
                'phuong_thuc_thanh_toan' => 'COD',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors(['so_nha_duong', 'province_code', 'ward_code']);
    }

    private function createCartWithBook(NguoiDung $customer): void
    {
        $book = Sach::create([
            'tieu_de' => 'Sách kiểm thử địa chỉ checkout',
            'duong_dan_tinh' => 'sach-kiem-thu-dia-chi-checkout',
            'gia_ban' => 50000,
            'dang_hoat_dong' => true,
        ]);
        KhoHang::create([
            'id_sach' => $book->id,
            'so_luong_ton' => 5,
            'so_luong_dat_truoc' => 0,
        ]);

        $cart = GioHang::create(['id_nguoi_dung' => $customer->id]);
        $cart->chiTietGioHang()->create(['id_sach' => $book->id, 'so_luong' => 1]);
    }
}
