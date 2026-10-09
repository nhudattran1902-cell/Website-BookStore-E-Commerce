<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('sach', function (Blueprint $table): void {
            $table->fullText(['tieu_de', 'mo_ta'], 'sach_content_fulltext');
        });

        Schema::table('tac_gia', function (Blueprint $table): void {
            $table->fullText('ten_tac_gia', 'tac_gia_name_fulltext');
        });

        Schema::table('nha_xuat_ban', function (Blueprint $table): void {
            $table->fullText('ten_nxb', 'nha_xuat_ban_name_fulltext');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('sach', function (Blueprint $table): void {
            $table->dropFullText('sach_content_fulltext');
        });

        Schema::table('tac_gia', function (Blueprint $table): void {
            $table->dropFullText('tac_gia_name_fulltext');
        });

        Schema::table('nha_xuat_ban', function (Blueprint $table): void {
            $table->dropFullText('nha_xuat_ban_name_fulltext');
        });
    }
};
