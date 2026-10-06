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
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('chuoi_nho_dang_nhap', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table): void {
            $table->dropColumn(['email_verified_at', 'chuoi_nho_dang_nhap']);
        });
    }
};
