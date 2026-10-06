<?php

namespace Tests\Feature\Http\Controllers\Customer;

use App\Models\GioHang;
use App\Models\NguoiDung;
use App\Models\Sach;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajax_add_returns_updated_cart_count_and_persists_quantity(): void
    {
        /** @var NguoiDung $customer */
        $customer = NguoiDung::factory()->createOne(['ho_ten' => 'Nguyen Van A']);
        $book = $this->createBookWithStock(5);

        $response = $this->actingAs($customer)->postJson(route('cart.add'), [
            'id_sach' => $book->id,
            'so_luong' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('book_name', $book->tieu_de)
            ->assertJsonPath('cart_count', 2)
            ->assertJsonPath('message', 'Đã thêm '.$book->tieu_de.' vào giỏ hàng!');

        $this->assertDatabaseHas('chi_tiet_gio_hang', [
            'id_sach' => $book->id,
            'so_luong' => 2,
        ]);
    }

    public function test_add_rejects_quantity_above_stock_without_creating_cart(): void
    {
        /** @var NguoiDung $customer */
        $customer = NguoiDung::factory()->createOne(['ho_ten' => 'Nguyen Van B']);
        $book = $this->createBookWithStock(1);

        $response = $this->actingAs($customer)->postJson(route('cart.add'), [
            'id_sach' => $book->id,
            'so_luong' => 2,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('so_luong');

        $this->assertDatabaseMissing('gio_hang', ['id_nguoi_dung' => $customer->id]);
        $this->assertDatabaseMissing('chi_tiet_gio_hang', ['id_sach' => $book->id]);
    }

    public function test_add_rejects_book_with_no_available_stock(): void
    {
        /** @var NguoiDung $customer */
        $customer = NguoiDung::factory()->createOne(['ho_ten' => 'Nguyen Van C']);
        $book = $this->createBookWithStock(0);

        $response = $this->actingAs($customer)->postJson(route('cart.add'), [
            'id_sach' => $book->id,
            'so_luong' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('so_luong');

        $this->assertDatabaseMissing('gio_hang', ['id_nguoi_dung' => $customer->id]);
    }

    public function test_add_rejects_quantity_above_available_stock_after_reservations(): void
    {
        /** @var NguoiDung $customer */
        $customer = NguoiDung::factory()->createOne(['ho_ten' => 'Nguyen Van D']);
        $book = $this->createBookWithStock(5, 3);

        $response = $this->actingAs($customer)->postJson(route('cart.add'), [
            'id_sach' => $book->id,
            'so_luong' => 3,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('so_luong');

        $this->assertDatabaseMissing('gio_hang', ['id_nguoi_dung' => $customer->id]);
        $this->assertDatabaseMissing('chi_tiet_gio_hang', ['id_sach' => $book->id]);
    }

    public function test_update_rejects_quantity_above_available_stock_after_reservations(): void
    {
        /** @var NguoiDung $customer */
        $customer = NguoiDung::factory()->createOne(['ho_ten' => 'Nguyen Van E']);
        $book = $this->createBookWithStock(5, 3);
        $cart = GioHang::create(['id_nguoi_dung' => $customer->id]);
        $item = $cart->chiTietGioHang()->create([
            'id_sach' => $book->id,
            'so_luong' => 2,
        ]);

        $response = $this->actingAs($customer)->putJson(route('cart.update', $item->id), [
            'so_luong' => 3,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('so_luong');

        $this->assertDatabaseHas('chi_tiet_gio_hang', [
            'id' => $item->id,
            'so_luong' => 2,
        ]);
    }

    public function test_update_persists_quantity_within_available_stock(): void
    {
        /** @var NguoiDung $customer */
        $customer = NguoiDung::factory()->createOne(['ho_ten' => 'Nguyen Van F']);
        $book = $this->createBookWithStock(5, 3);
        $cart = GioHang::create(['id_nguoi_dung' => $customer->id]);
        $item = $cart->chiTietGioHang()->create([
            'id_sach' => $book->id,
            'so_luong' => 1,
        ]);

        $response = $this->actingAs($customer)->putJson(route('cart.update', $item->id), [
            'so_luong' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('chi_tiet_gio_hang', [
            'id' => $item->id,
            'so_luong' => 2,
        ]);
    }

    private function createBookWithStock(int $stock, int $reserved = 0): Sach
    {
        $book = Sach::create([
            'tieu_de' => 'Test Book',
            'duong_dan_tinh' => 'test-book-'.$stock,
            'gia_ban' => 25000,
            'dang_hoat_dong' => true,
        ]);

        $book->khoHang()->create([
            'so_luong_ton' => $stock,
            'so_luong_dat_truoc' => $reserved,
        ]);

        return $book;
    }
}
