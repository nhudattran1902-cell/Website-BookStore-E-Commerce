<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HomePageWithoutDiscountColumnTest extends TestCase
{
    public function test_weekly_deals_query_ignores_discount_filter_when_column_is_missing(): void
    {
        Schema::dropAllTables();

        Schema::create('sach', function ($table) {
            $table->id();
            $table->string('tieu_de');
            $table->string('duong_dan_tinh')->unique();
            $table->decimal('gia_ban', 12, 0);
            $table->boolean('dang_hoat_dong')->default(true);
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        \DB::table('sach')->insert([
            'tieu_de' => 'Sample Book',
            'duong_dan_tinh' => 'sample-book',
            'gia_ban' => 120000,
            'dang_hoat_dong' => true,
            'ngay_tao' => now(),
            'ngay_cap_nhat' => now(),
        ]);

        $this->assertFalse(Schema::hasColumn('sach', 'gia_khuyen_mai'));

        $query = (new HomeController)->weeklyDealsQuery();

        $this->assertSame(1, $query->count());
    }
}
