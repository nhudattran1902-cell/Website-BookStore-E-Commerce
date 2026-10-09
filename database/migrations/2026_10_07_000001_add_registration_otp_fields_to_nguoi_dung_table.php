<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table): void {
            $table->char('registration_otp_hash', 64)->nullable();
            $table->timestamp('registration_otp_expires_at')->nullable();
            $table->unsignedTinyInteger('registration_otp_attempts')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table): void {
            $table->dropColumn([
                'registration_otp_hash',
                'registration_otp_expires_at',
                'registration_otp_attempts',
            ]);
        });
    }
};
