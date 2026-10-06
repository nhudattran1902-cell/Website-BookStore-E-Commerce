<?php

namespace Tests\Unit\Services;

use App\Models\DonHang;
use App\Models\NguoiDung;
use App\Models\Sach;
use App\Services\BookshopAssistant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookshopAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_assistant_response_using_gemini_generate_content(): void
    {
        config([
            'services.bookshop_ai.api_key' => 'test-key',
            'services.bookshop_ai.base_url' => 'https://ai.example.test/v1beta',
            'services.bookshop_ai.model' => 'test-model',
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'ai.example.test/v1beta/models/test-model:generateContent' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => '  Xin chào từ BOOK & BOX.  ']]]],
                ],
            ]),
        ]);

        $answer = app(BookshopAssistant::class)->reply('Xin chào', [
            ['role' => 'assistant', 'content' => 'Bạn cần hỗ trợ gì?'],
        ]);

        $this->assertSame('Xin chào từ BOOK & BOX.', $answer);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://ai.example.test/v1beta/models/test-model:generateContent'
            && $request->hasHeader('x-goog-api-key', 'test-key')
            && str_contains($request['systemInstruction']['parts'][0]['text'], 'BOOK & BOX')
            && $request['contents'][0]['role'] === 'user'
            && $request['contents'][0]['parts'][0]['text'] === 'Xin chào');
    }

    public function test_returns_null_when_no_api_key_is_configured(): void
    {
        config(['services.bookshop_ai.api_key' => null]);
        Http::preventStrayRequests();

        $answer = app(BookshopAssistant::class)->reply('Xin chào');

        $this->assertNull($answer);
        Http::assertNothingSent();
    }

    public function test_returns_null_when_ai_provider_fails(): void
    {
        config([
            'services.bookshop_ai.api_key' => 'test-key',
            'services.bookshop_ai.base_url' => 'https://ai.example.test/v1beta',
            'services.bookshop_ai.model' => 'test-model',
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'ai.example.test/v1beta/models/test-model:generateContent' => Http::response([], 503),
        ]);

        $answer = app(BookshopAssistant::class)->reply('Xin chào');

        $this->assertNull($answer);
        Http::assertSentCount(3);
    }

    public function test_retries_when_ai_provider_is_temporarily_unavailable(): void
    {
        config([
            'services.bookshop_ai.api_key' => 'test-key',
            'services.bookshop_ai.base_url' => 'https://ai.example.test/v1beta',
            'services.bookshop_ai.model' => 'test-model',
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'ai.example.test/v1beta/models/test-model:generateContent' => Http::sequence()
                ->push([], 503)
                ->push([
                    'candidates' => [
                        ['content' => ['parts' => [['text' => 'Gemini đã hoạt động lại.']]]],
                    ],
                ]),
        ]);

        $answer = app(BookshopAssistant::class)->reply('Xin chào');

        $this->assertSame('Gemini đã hoạt động lại.', $answer);
        Http::assertSentCount(2);
    }

    public function test_price_recommendation_only_sends_books_within_the_requested_budget(): void
    {
        config([
            'services.bookshop_ai.api_key' => 'test-key',
            'services.bookshop_ai.base_url' => 'https://ai.example.test/v1beta',
            'services.bookshop_ai.model' => 'test-model',
        ]);
        Sach::create([
            'tieu_de' => 'Sách vừa túi tiền',
            'duong_dan_tinh' => 'sach-vua-tui-tien',
            'gia_ban' => 80000,
            'dang_hoat_dong' => true,
        ]);
        Sach::create([
            'tieu_de' => 'Sách vượt ngân sách',
            'duong_dan_tinh' => 'sach-vuot-ngan-sach',
            'gia_ban' => 150000,
            'dang_hoat_dong' => true,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'ai.example.test/v1beta/models/test-model:generateContent' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Gợi ý phù hợp.']]]],
                ],
            ]),
        ]);

        app(BookshopAssistant::class)->reply('Gợi ý sách dưới 100.000 đồng');

        Http::assertSent(function (Request $request): bool {
            $instruction = $request['systemInstruction']['parts'][0]['text'];

            return str_contains($instruction, 'Sách vừa túi tiền')
                && ! str_contains($instruction, 'Sách vượt ngân sách');
        });
    }

    public function test_unknown_title_does_not_fall_back_to_unrelated_bestsellers(): void
    {
        config([
            'services.bookshop_ai.api_key' => 'test-key',
            'services.bookshop_ai.base_url' => 'https://ai.example.test/v1beta',
            'services.bookshop_ai.model' => 'test-model',
        ]);
        Sach::create([
            'tieu_de' => 'Sách bán chạy không liên quan',
            'duong_dan_tinh' => 'sach-ban-chay-khong-lien-quan',
            'gia_ban' => 90000,
            'ban_chay' => true,
            'dang_hoat_dong' => true,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'ai.example.test/v1beta/models/test-model:generateContent' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Chưa tìm thấy sách này.']]]],
                ],
            ]),
        ]);

        app(BookshopAssistant::class)->reply('Có sách Cuốn sách không tồn tại XYZ không?');

        Http::assertSent(function (Request $request): bool {
            $instruction = $request['systemInstruction']['parts'][0]['text'];

            return str_contains($instruction, 'Dữ liệu sách liên quan (JSON):')
                && str_ends_with(trim($instruction), '[]')
                && ! str_contains($instruction, 'Sách bán chạy không liên quan');
        });
    }

    public function test_returns_latest_order_status_for_the_authenticated_customer(): void
    {
        config(['services.bookshop_ai.api_key' => null]);
        $customer = NguoiDung::factory()->create();
        DonHang::create([
            'ma_don_hang' => 'DH-2026-001',
            'id_nguoi_dung' => $customer->id,
            'tong_tien' => 100000,
            'thanh_tien' => 100000,
            'trang_thai' => 'dang_giao',
            'ma_van_don' => 'GHN123456',
        ]);
        Http::preventStrayRequests();

        $answer = app(BookshopAssistant::class)->reply('Đơn hàng của tôi đang ở đâu?', userId: $customer->id);

        $this->assertSame(
            'Đơn DH-2026-001 hiện ở trạng thái: Đang giao hàng. Mã vận đơn: GHN123456. Bạn có thể mở mục Đơn hàng của tôi để xem chi tiết.',
            $answer
        );
        Http::assertNothingSent();
    }
}
