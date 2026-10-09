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
        Schema::table('sach', function (Blueprint $table): void {
            if (! Schema::hasColumn('sach', 'loai_bia')) {
                $table->string('loai_bia', 20)->nullable();
            }
            if (! Schema::hasColumn('sach', 'khoi_luong_gram')) {
                $table->unsignedInteger('khoi_luong_gram')->nullable();
            }
            if (! Schema::hasColumn('sach', 'chieu_rong_mm')) {
                $table->decimal('chieu_rong_mm', 7, 2)->nullable();
            }
            if (! Schema::hasColumn('sach', 'chieu_cao_mm')) {
                $table->decimal('chieu_cao_mm', 7, 2)->nullable();
            }
            if (! Schema::hasColumn('sach', 'do_day_mm')) {
                $table->decimal('do_day_mm', 7, 2)->nullable();
            }
            if (! Schema::hasColumn('sach', 'ngon_ngu')) {
                $table->string('ngon_ngu', 100)->nullable();
            }
            if (! Schema::hasColumn('sach', 'lan_tai_ban')) {
                $table->unsignedSmallInteger('lan_tai_ban')->nullable();
            }
            if (! Schema::hasColumn('sach', 'nha_cung_cap')) {
                $table->string('nha_cung_cap', 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sach', function (Blueprint $table): void {
            $columns = [
                'loai_bia',
                'khoi_luong_gram',
                'chieu_rong_mm',
                'chieu_cao_mm',
                'do_day_mm',
                'ngon_ngu',
                'lan_tai_ban',
                'nha_cung_cap',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('sach', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
