<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('nguoi_dung')) {
            return;
        }

        Schema::table('nguoi_dung', function (Blueprint $table): void {
            if (! Schema::hasColumn('nguoi_dung', 'admin_email_otp_hash')) {
                $table->char('admin_email_otp_hash', 64)->nullable();
            }
            if (! Schema::hasColumn('nguoi_dung', 'admin_email_otp_expires_at')) {
                $table->timestamp('admin_email_otp_expires_at')->nullable();
            }
            if (! Schema::hasColumn('nguoi_dung', 'admin_email_otp_attempts')) {
                $table->unsignedTinyInteger('admin_email_otp_attempts')->default(0);
            }
        });

        $adminEmail = DB::table('nguoi_dung')
            ->where('email', 'nhudattran1902@gmail.com')
            ->first();

        if ($adminEmail) {
            $adminRoleId = Schema::hasTable('vai_tro')
                ? DB::table('vai_tro')->where('ten_vai_tro', 'admin')->value('id')
                : null;

            if ($adminRoleId && Schema::hasTable('vai_tro_nguoi_dung')) {
                DB::table('vai_tro_nguoi_dung')->updateOrInsert([
                    'id_nguoi_dung' => $adminEmail->id,
                    'id_vai_tro' => $adminRoleId,
                ]);
            }
        } else {
            DB::table('nguoi_dung')
                ->where('email', 'admin@bookbox.com')
                ->update(['email' => 'nhudattran1902@gmail.com']);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('nguoi_dung')) {
            return;
        }

        $columns = array_filter([
            Schema::hasColumn('nguoi_dung', 'admin_email_otp_hash') ? 'admin_email_otp_hash' : null,
            Schema::hasColumn('nguoi_dung', 'admin_email_otp_expires_at') ? 'admin_email_otp_expires_at' : null,
            Schema::hasColumn('nguoi_dung', 'admin_email_otp_attempts') ? 'admin_email_otp_attempts' : null,
        ]);

        if ($columns !== []) {
            Schema::table('nguoi_dung', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
