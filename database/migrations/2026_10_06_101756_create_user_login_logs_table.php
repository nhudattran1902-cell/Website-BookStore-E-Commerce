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
        Schema::create('user_login_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->cascadeOnDelete();
            $table->string('email', 150);
            $table->string('ket_qua', 20);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('attempted_at')->useCurrent();
            $table->index(['id_nguoi_dung', 'attempted_at']);
            $table->index(['email', 'attempted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_login_logs');
    }
};
