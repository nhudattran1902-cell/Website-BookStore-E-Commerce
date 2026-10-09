<?php

namespace Tests\Feature\Customer;

use App\Models\NguoiDung;
use App\Models\Sach;
use App\Models\TheoDoiHang;
use App\Notifications\BookAvailableNotification;
use App\Services\StockAvailabilityNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StockAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_to_track_a_book(): void
    {
        $book = $this->createBook();

        $this->post(route('customer.stock-alerts.store', $book))
            ->assertRedirect(route('login'));
    }

    public function test_customer_can_track_an_unavailable_book_once(): void
    {
        $customer = NguoiDung::factory()->createOne();
        $book = $this->createBook();
        $book->khoHang()->create(['so_luong_ton' => 5, 'so_luong_dat_truoc' => 5]);

        $this->actingAs($customer)
            ->post(route('customer.stock-alerts.store', $book))
            ->assertRedirect();
        $this->actingAs($customer)
            ->post(route('customer.stock-alerts.store', $book))
            ->assertRedirect();

        $this->assertDatabaseCount('theo_doi_hang', 1);
        $this->assertDatabaseHas('theo_doi_hang', [
            'id_nguoi_dung' => $customer->id,
            'id_sach' => $book->id,
            'da_thong_bao_at' => null,
        ]);
    }

    public function test_customer_cannot_track_a_book_that_is_available(): void
    {
        $customer = NguoiDung::factory()->createOne();
        $book = $this->createBook();
        $book->khoHang()->create(['so_luong_ton' => 2, 'so_luong_dat_truoc' => 0]);

        $this->actingAs($customer)
            ->post(route('customer.stock-alerts.store', $book))
            ->assertRedirect();

        $this->assertDatabaseCount('theo_doi_hang', 0);
    }

    public function test_customer_cannot_remove_another_customers_tracking(): void
    {
        $owner = NguoiDung::factory()->createOne();
        $anotherCustomer = NguoiDung::factory()->createOne();
        $book = $this->createBook();
        $tracking = TheoDoiHang::create([
            'id_nguoi_dung' => $owner->id,
            'id_sach' => $book->id,
        ]);

        $this->actingAs($anotherCustomer)
            ->delete(route('customer.stock-alerts.destroy', $tracking->id))
            ->assertNotFound();

        $this->assertDatabaseHas('theo_doi_hang', ['id' => $tracking->id]);
    }

    public function test_customer_is_notified_only_when_the_book_has_available_stock(): void
    {
        Notification::fake();

        $customer = NguoiDung::factory()->createOne();
        $book = $this->createBook();
        $stock = $book->khoHang()->create(['so_luong_ton' => 3, 'so_luong_dat_truoc' => 3]);
        $tracking = TheoDoiHang::create([
            'id_nguoi_dung' => $customer->id,
            'id_sach' => $book->id,
        ]);
        $notifier = app(StockAvailabilityNotifier::class);

        $notifier->notifyIfAvailable($book->id);

        Notification::assertNothingSent();
        $this->assertNull($tracking->fresh()->da_thong_bao_at);

        $stock->update(['so_luong_ton' => 4]);
        $notifier->notifyIfAvailable($book->id);

        Notification::assertSentTo($customer, BookAvailableNotification::class);
        $this->assertNotNull($tracking->fresh()->da_thong_bao_at);

        $notifier->notifyIfAvailable($book->id);
        Notification::assertSentTo($customer, BookAvailableNotification::class, 1);
    }

    private function createBook(): Sach
    {
        return Sach::create([
            'tieu_de' => 'Sách sắp có hàng kiểm thử',
            'duong_dan_tinh' => 'sach-sap-co-hang-kiem-thu',
            'gia_ban' => 50000,
            'dang_hoat_dong' => true,
        ]);
    }
}
