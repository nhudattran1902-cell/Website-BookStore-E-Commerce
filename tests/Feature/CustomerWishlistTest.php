<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use App\Models\Sach;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerWishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_wishlist(): void
    {
        $response = $this->get(route('customer.wishlist.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_customer_can_save_and_view_favorite_book(): void
    {
        $customer = NguoiDung::factory()->createOne();
        $book = $this->createBook();

        $this->actingAs($customer)
            ->post(route('customer.wishlist.store', $book))
            ->assertRedirect();

        $this->assertDatabaseHas('sach_yeu_thich', [
            'id_nguoi_dung' => $customer->id,
            'id_sach' => $book->id,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.wishlist.index'))
            ->assertOk()
            ->assertSee($book->tieu_de);
    }

    public function test_saving_same_book_twice_does_not_duplicate_favorite(): void
    {
        $customer = NguoiDung::factory()->createOne();
        $book = $this->createBook();

        $this->actingAs($customer)->post(route('customer.wishlist.store', $book));
        $this->actingAs($customer)->post(route('customer.wishlist.store', $book));

        $this->assertDatabaseCount('sach_yeu_thich', 1);
    }

    public function test_customer_can_remove_book_from_wishlist(): void
    {
        $customer = NguoiDung::factory()->createOne();
        $book = $this->createBook();
        $customer->sachYeuThich()->attach($book->id);

        $this->actingAs($customer)
            ->delete(route('customer.wishlist.destroy', $book))
            ->assertRedirect();

        $this->assertDatabaseMissing('sach_yeu_thich', [
            'id_nguoi_dung' => $customer->id,
            'id_sach' => $book->id,
        ]);
    }

    private function createBook(): Sach
    {
        return Sach::create([
            'tieu_de' => 'Sách yêu thích kiểm thử',
            'duong_dan_tinh' => 'sach-yeu-thich-kiem-thu',
            'gia_ban' => 50000,
            'dang_hoat_dong' => true,
        ]);
    }
}
