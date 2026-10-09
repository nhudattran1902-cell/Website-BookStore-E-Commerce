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
        Schema::create('theo_doi_hang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_nguoi_dung');
            $table->unsignedBigInteger('id_sach');
            $table->timestamp('da_thong_bao_at')->nullable();
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['id_nguoi_dung', 'id_sach']);
            $table->foreign('id_nguoi_dung')->references('id')->on('nguoi_dung')->cascadeOnDelete();
            $table->foreign('id_sach')->references('id')->on('sach')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theo_doi_hang');
    }
};
