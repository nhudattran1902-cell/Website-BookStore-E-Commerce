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
        if (! Schema::hasTable('tin_nhan_chat')) {
            Schema::create('tin_nhan_chat', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_nguoi_dung')->nullable()->constrained('nguoi_dung')->nullOnDelete();
                $table->string('session_id')->nullable()->index(); // Dành cho khách vãng lai
                $table->foreignId('id_admin')->nullable()->constrained('nguoi_dung')->nullOnDelete();
                $table->enum('nguoi_gui', ['khach_hang', 'admin'])->default('khach_hang');
                $table->text('noi_dung');
                $table->boolean('da_doc')->default(false);
                $table->timestamp('ngay_tao')->useCurrent();
                $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tin_nhan_chat');
    }
};
