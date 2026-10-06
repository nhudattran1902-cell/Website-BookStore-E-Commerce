<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('don_hang')) {
            return;
        }

        Schema::table('don_hang', function (Blueprint $table): void {
            if (! Schema::hasColumn('don_hang', 'don_vi_van_chuyen')) {
                $table->string('don_vi_van_chuyen', 50)->nullable()->after('trang_thai');
            }
            if (! Schema::hasColumn('don_hang', 'ma_van_don')) {
                $table->string('ma_van_don', 100)->nullable()->after('don_vi_van_chuyen');
            }
            if (! Schema::hasColumn('don_hang', 'tracking_url')) {
                $table->text('tracking_url')->nullable()->after('ma_van_don');
            }
        });
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['don_vi_van_chuyen', 'ma_van_don', 'tracking_url'],
            fn (string $column): bool => Schema::hasColumn('don_hang', $column),
        ));

        if ($columns !== []) {
            Schema::table('don_hang', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
