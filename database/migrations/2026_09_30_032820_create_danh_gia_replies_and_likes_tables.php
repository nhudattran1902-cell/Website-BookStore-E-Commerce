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
        if (! Schema::hasTable('danh_gia_binh_luan')) {
            Schema::create('danh_gia_binh_luan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('danh_gia_id')->constrained('danh_gia')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('nguoi_dung')->cascadeOnDelete();
                $table->text('noi_dung');
                $table->timestamp('ngay_tao')->useCurrent();
                $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
                $table->index(['danh_gia_id', 'ngay_tao']);
            });
        }

        if (! Schema::hasTable('danh_gia_luot_thich')) {
            Schema::create('danh_gia_luot_thich', function (Blueprint $table) {
                $table->id();
                $table->foreignId('danh_gia_id')->constrained('danh_gia')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('nguoi_dung')->cascadeOnDelete();
                $table->timestamp('ngay_tao')->useCurrent();
                $table->unique(['danh_gia_id', 'user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danh_gia_luot_thich');
        Schema::dropIfExists('danh_gia_binh_luan');
    }
};
