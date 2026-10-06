<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardWithoutChatTableTest extends TestCase
{
    public function test_dashboard_works_when_chat_table_is_missing(): void
    {
        Schema::dropAllTables();

        Schema::create('don_hang', function ($table) {
            $table->id();
            $table->string('ma_don_hang')->unique();
            $table->string('trang_thai')->default('cho_xu_ly');
            $table->decimal('thanh_tien', 14, 0)->default(0);
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('kho_hang', function ($table) {
            $table->id();
            $table->integer('so_luong_ton')->default(0);
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('sach', function ($table) {
            $table->id();
            $table->string('tieu_de');
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('nguoi_dung', function ($table) {
            $table->id();
            $table->string('ho_ten')->nullable();
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        $this->assertFalse(Schema::hasTable('tin_nhan_chat'));

        $view = (new DashboardController)->index();

        $this->assertSame('admin.dashboard', $view->name());
    }
}
