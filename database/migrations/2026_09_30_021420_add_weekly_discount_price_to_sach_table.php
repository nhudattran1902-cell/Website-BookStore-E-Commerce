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
        if (! Schema::hasTable('sach') || Schema::hasColumn('sach', 'gia_khuyen_mai')) {
            return;
        }

        Schema::table('sach', function (Blueprint $table) {
            $table->decimal('gia_khuyen_mai', 12, 0)->nullable()->after('gia_ban');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sach') && Schema::hasColumn('sach', 'gia_khuyen_mai')) {
            Schema::table('sach', function (Blueprint $table) {
                $table->dropColumn('gia_khuyen_mai');
            });
        }
    }
};
