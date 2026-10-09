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
            if (! Schema::hasColumn('nguoi_dung', 'admin_password_reset_otp_hash')) {
                $table->char('admin_password_reset_otp_hash', 64)->nullable();
            }

            if (! Schema::hasColumn('nguoi_dung', 'admin_password_reset_otp_expires_at')) {
                $table->timestamp('admin_password_reset_otp_expires_at')->nullable();
            }

            if (! Schema::hasColumn('nguoi_dung', 'admin_password_reset_otp_attempts')) {
                $table->unsignedTinyInteger('admin_password_reset_otp_attempts')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = array_filter([
            Schema::hasColumn('nguoi_dung', 'admin_password_reset_otp_hash') ? 'admin_password_reset_otp_hash' : null,
            Schema::hasColumn('nguoi_dung', 'admin_password_reset_otp_expires_at') ? 'admin_password_reset_otp_expires_at' : null,
            Schema::hasColumn('nguoi_dung', 'admin_password_reset_otp_attempts') ? 'admin_password_reset_otp_attempts' : null,
        ]);

        if ($columns !== []) {
            Schema::table('nguoi_dung', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
