<?php

namespace Tests\Feature;

use App\Http\Controllers\ChatController;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminChatSessionIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_index_accepts_session_id_conv_id(): void
    {
        $user = NguoiDung::factory()->create([
            'ho_ten' => 'Trần Minh Anh',
            'email' => 'minhanh@example.com',
        ]);

        DB::table('tin_nhan_chat')->insert([
            [
                'id_nguoi_dung' => null,
                'session_id' => '5eFtwiME8qUbhbAdRSFjLjAJPuOluH6wf2TtnhNk',
                'kenh' => 'cskh',
                'ten_khach' => 'Nguyễn Văn Khách',
                'nguoi_gui' => 'khach_hang',
                'da_doc' => false,
                'noi_dung' => 'Tôi cần nhân viên hỗ trợ',
                'ngay_tao' => now(),
                'ngay_cap_nhat' => now(),
            ],
            [
                'id_nguoi_dung' => null,
                'session_id' => '5eFtwiME8qUbhbAdRSFjLjAJPuOluH6wf2TtnhNk',
                'kenh' => 'chatbot',
                'ten_khach' => null,
                'nguoi_gui' => 'khach_hang',
                'da_doc' => false,
                'noi_dung' => 'Gợi ý sách cho tôi',
                'ngay_tao' => now(),
                'ngay_cap_nhat' => now(),
            ],
            [
                'id_nguoi_dung' => $user->id,
                'session_id' => null,
                'kenh' => 'cskh',
                'ten_khach' => null,
                'nguoi_gui' => 'khach_hang',
                'da_doc' => false,
                'noi_dung' => 'Tôi cần đổi địa chỉ nhận hàng',
                'ngay_tao' => now(),
                'ngay_cap_nhat' => now(),
            ],
        ]);

        $response = (new ChatController)->adminIndex(Request::create('/admin/chat', 'GET', ['conv_id' => '5eFtwiME8qUbhbAdRSFjLjAJPuOluH6wf2TtnhNk']));

        $this->assertSame('admin.chat.index', $response->name());
        $this->assertSame('Nguyễn Văn Khách', $response->getData()['activeCustomerName']);
        $this->assertCount(1, $response->getData()['messages']);
        $this->assertCount(2, $response->getData()['conversations']);
        $this->assertSame(
            'Trần Minh Anh',
            $response->getData()['conversations']->firstWhere('id_nguoi_dung', $user->id)->nguoiDung->ho_ten
        );
    }
}
