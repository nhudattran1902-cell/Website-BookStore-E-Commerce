<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('don_hang', function (Blueprint $table): void {
            $table->unique(['id_nguoi_dung', 'id_ma_giam_gia'], 'don_hang_nguoi_dung_ma_giam_gia_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('don_hang', function (Blueprint $table): void {
            $table->dropUnique('don_hang_nguoi_dung_ma_giam_gia_unique');
        });
    }
};
