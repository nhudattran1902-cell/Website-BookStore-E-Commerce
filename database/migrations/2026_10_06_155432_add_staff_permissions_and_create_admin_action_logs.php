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
        Schema::table('nguoi_dung', function (Blueprint $table): void {
            $table->json('quyen_han')->nullable();
        });

        Schema::create('admin_action_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_nguoi_thuc_hien')->nullable()->constrained('nguoi_dung')->nullOnDelete();
            $table->string('hanh_dong', 100);
            $table->string('doi_tuong', 100);
            $table->string('doi_tuong_id', 100)->nullable();
            $table->json('du_lieu')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['id_nguoi_thuc_hien', 'created_at']);
            $table->index(['hanh_dong', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_action_logs');

        Schema::table('nguoi_dung', function (Blueprint $table): void {
            $table->dropColumn('quyen_han');
        });
    }
};
