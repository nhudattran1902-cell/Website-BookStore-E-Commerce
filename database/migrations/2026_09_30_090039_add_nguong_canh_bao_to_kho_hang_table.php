<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kho_hang') && ! Schema::hasColumn('kho_hang', 'nguong_canh_bao')) {
            Schema::table('kho_hang', function (Blueprint $table) {
                $table->integer('nguong_canh_bao')->default(5)->after('so_luong_ton');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kho_hang') && Schema::hasColumn('kho_hang', 'nguong_canh_bao')) {
            Schema::table('kho_hang', function (Blueprint $table) {
                $table->dropColumn('nguong_canh_bao');
            });
        }
    }
};
