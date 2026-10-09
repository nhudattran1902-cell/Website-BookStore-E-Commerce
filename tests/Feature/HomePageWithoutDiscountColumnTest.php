<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HomePageWithoutDiscountColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_deals_query_ignores_discount_filter_when_column_is_missing(): void
    {
        Schema::table('sach', function ($table) {
            $table->dropColumn('gia_khuyen_mai');
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
