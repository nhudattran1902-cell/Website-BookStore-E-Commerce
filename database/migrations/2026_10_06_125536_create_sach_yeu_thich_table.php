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
        Schema::create('sach_yeu_thich', function (Blueprint $table) {
            $table->unsignedBigInteger('id_nguoi_dung');
            $table->unsignedBigInteger('id_sach');
            $table->timestamp('ngay_tao')->useCurrent();
            $table->primary(['id_nguoi_dung', 'id_sach']);
            $table->foreign('id_nguoi_dung')->references('id')->on('nguoi_dung')->cascadeOnDelete();
            $table->foreign('id_sach')->references('id')->on('sach')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sach_yeu_thich');
    }
};
