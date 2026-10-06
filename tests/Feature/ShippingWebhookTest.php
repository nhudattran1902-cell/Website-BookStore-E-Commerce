<?php

namespace Tests\Feature;

use App\Models\ChiTietDonHang;
use App\Models\DonHang;
use App\Models\NguoiDung;
use App\Models\Sach;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_shipping_webhook_updates_order_once_and_logs_location(): void
    {
        $secret = 'ghn-webhook-test-secret';
        config(['services.shipping.ghn.webhook_secret' => $secret]);
        $customer = NguoiDung::factory()->createOne();
        $book = Sach::create([
            'tieu_de' => 'Shipping test book',
            'duong_dan_tinh' => 'shipping-test-book',
            'gia_ban' => 30000,
            'dang_hoat_dong' => true,
        ]);
        $book->khoHang()->create(['so_luong_ton' => 4, 'so_luong_dat_truoc' => 0]);
        $order = DonHang::create([
            'ma_don_hang' => 'SHP-'.str()->random(10),
            'id_nguoi_dung' => $customer->id,
            'tong_tien' => 30000,
            'so_tien_giam_gia' => 0,
            'thanh_tien' => 30000,
            'trang_thai' => 'dang_xu_ly',
            'don_vi_van_chuyen' => 'GHN',
            'ma_van_don' => 'TRACK-123',
        ]);
        ChiTietDonHang::create([
            'id_don_hang' => $order->id,
            'id_sach' => $book->id,
            'don_gia' => 30000,
            'so_luong' => 1,
            'thanh_tien' => 30000,
        ]);
        $payload = [
            'order_code' => 'TRACK-123',
            'status' => 'delivering',
            'event_id' => 'GHN-EVENT-1',
            'location' => 'Kho trung chuyen test',
            'note' => 'Đang giao cho bưu tá',
        ];

        $this->postSignedWebhook($payload, $secret)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('duplicate', false);

        $this->assertSame('dang_giao', $order->fresh()->trang_thai);
        $this->assertDatabaseHas('lich_su_don_hang', [
            'id_don_hang' => $order->id,
            'nguon' => 'shipping_ghn',
            'ma_su_kien' => 'GHN-EVENT-1',
            'vi_tri' => 'Kho trung chuyen test',
        ]);

        $this->postSignedWebhook($payload, $secret)
            ->assertOk()
            ->assertJsonPath('duplicate', true);
        $this->assertDatabaseCount('lich_su_don_hang', 1);

        $this->postSignedWebhook($payload, 'wrong-secret')
            ->assertUnauthorized();
        $this->assertSame('dang_giao', $order->fresh()->trang_thai);
    }

    private function postSignedWebhook(array $payload, string $secret)
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', $body, $secret);

        return $this->call(
            'POST',
            route('api.webhooks.shipping', 'ghn'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
            ],
            $body,
        );
    }
}
