<?php

namespace Tests\Feature;

use App\Http\Controllers\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminChatSessionIdTest extends TestCase
{
    public function test_admin_index_accepts_session_id_conv_id(): void
    {
        Schema::dropAllTables();

        Schema::create('nguoi_dung', function ($table) {
            $table->id();
            $table->string('ho_ten');
            $table->string('email');
        });

        Schema::create('tin_nhan_chat', function ($table) {
            $table->id();
            $table->unsignedBigInteger('id_nguoi_dung')->nullable();
            $table->string('session_id')->nullable();
            $table->string('kenh')->default('cskh');
            $table->string('ten_khach')->nullable();
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->string('nguoi_gui')->default('khach_hang');
            $table->boolean('da_doc')->default(false);
            $table->text('noi_dung')->nullable();
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        \DB::table('nguoi_dung')->insert([
            'id' => 7,
            'ho_ten' => 'Trần Minh Anh',
            'email' => 'minhanh@example.com',
        ]);

        \DB::table('tin_nhan_chat')->insert([
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
                'id_nguoi_dung' => 7,
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
            $response->getData()['conversations']->firstWhere('id_nguoi_dung', 7)->nguoiDung->ho_ten
        );
    }
}
