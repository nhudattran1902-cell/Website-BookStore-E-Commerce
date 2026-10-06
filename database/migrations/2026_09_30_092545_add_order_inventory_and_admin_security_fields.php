<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sach') && ! Schema::hasColumn('sach', 'gia_von')) {
            Schema::table('sach', function (Blueprint $table): void {
                $table->decimal('gia_von', 12, 0)->default(0);
            });
        }

        if (Schema::hasTable('nguoi_dung')) {
            Schema::table('nguoi_dung', function (Blueprint $table): void {
                if (! Schema::hasColumn('nguoi_dung', 'admin_totp_secret')) {
                    $table->text('admin_totp_secret')->nullable();
                }
                if (! Schema::hasColumn('nguoi_dung', 'admin_totp_confirmed_at')) {
                    $table->timestamp('admin_totp_confirmed_at')->nullable();
                }
                if (! Schema::hasColumn('nguoi_dung', 'admin_totp_last_counter')) {
                    $table->unsignedBigInteger('admin_totp_last_counter')->nullable();
                }
            });
        }

        if (Schema::hasTable('don_hang')) {
            Schema::table('don_hang', function (Blueprint $table): void {
                if (! Schema::hasColumn('don_hang', 'da_giu_ton')) {
                    $table->boolean('da_giu_ton')->default(false);
                }
                if (! Schema::hasColumn('don_hang', 'thanh_toan_het_han_at')) {
                    $table->timestamp('thanh_toan_het_han_at')->nullable()->index();
                }
            });
        }

        if (Schema::hasTable('lich_su_don_hang')) {
            Schema::table('lich_su_don_hang', function (Blueprint $table): void {
                if (! Schema::hasColumn('lich_su_don_hang', 'vi_tri')) {
                    $table->string('vi_tri', 255)->nullable();
                }
                if (! Schema::hasColumn('lich_su_don_hang', 'nguon')) {
                    $table->string('nguon', 30)->nullable();
                }
                if (! Schema::hasColumn('lich_su_don_hang', 'ma_su_kien')) {
                    $table->string('ma_su_kien', 120)->nullable();
                }
            });

            Schema::table('lich_su_don_hang', function (Blueprint $table): void {
                $table->unique(['nguon', 'ma_su_kien']);
            });
        }

        if (Schema::hasTable('thanh_toan')) {
            Schema::table('thanh_toan', function (Blueprint $table): void {
                $table->string('phuong_thuc_thanh_toan', 30)->default('COD')->change();
                $table->string('trang_thai', 30)->default('cho_thanh_toan')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('thanh_toan')) {
            Schema::table('thanh_toan', function (Blueprint $table): void {
                $table->enum('phuong_thuc_thanh_toan', ['COD', 'MoMo', 'VNPay', 'BankTransfer'])
                    ->default('COD')
                    ->change();
                $table->enum('trang_thai', ['cho_thanh_toan', 'da_thanh_toan', 'hoan_tien'])
                    ->default('cho_thanh_toan')
                    ->change();
            });
        }

        if (Schema::hasTable('lich_su_don_hang')) {
            Schema::table('lich_su_don_hang', function (Blueprint $table): void {
                $table->dropUnique(['nguon', 'ma_su_kien']);
                $table->dropColumn(['vi_tri', 'nguon', 'ma_su_kien']);
            });
        }

        if (Schema::hasTable('don_hang') && Schema::hasColumn('don_hang', 'thanh_toan_het_han_at')) {
            Schema::table('don_hang', function (Blueprint $table): void {
                $table->dropIndex(['thanh_toan_het_han_at']);
                $table->dropColumn(['da_giu_ton', 'thanh_toan_het_han_at']);
            });
        }

        if (Schema::hasTable('nguoi_dung')) {
            Schema::table('nguoi_dung', function (Blueprint $table): void {
                $table->dropColumn(['admin_totp_secret', 'admin_totp_confirmed_at', 'admin_totp_last_counter']);
            });
        }

        if (Schema::hasTable('sach') && Schema::hasColumn('sach', 'gia_von')) {
            Schema::table('sach', function (Blueprint $table): void {
                $table->dropColumn('gia_von');
            });
        }
    }
};
