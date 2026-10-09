<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $titlePrefixes = [
            'Hạt Giống Cho Tâm Hồn - Tập - Tập ' => 'Hạt Giống Cho Tâm Hồn - Tập ',
            'Dạy Con Làm Giàu - Tập - Tập ' => 'Dạy Con Làm Giàu - Tập ',
            'Kính Vạn Hoa - Tập - Tập ' => 'Kính Vạn Hoa - Tập ',
        ];

        foreach ($titlePrefixes as $incorrectPrefix => $correctPrefix) {
            $books = DB::table('sach')
                ->where('tieu_de', 'like', $incorrectPrefix.'%')
                ->get(['id', 'tieu_de']);

            foreach ($books as $book) {
                $correctTitle = $correctPrefix.substr($book->tieu_de, strlen($incorrectPrefix));
                $baseSlug = Str::slug($correctTitle);
                $correctSlug = $baseSlug;
                $suffix = 2;

                while (DB::table('sach')
                    ->where('duong_dan_tinh', $correctSlug)
                    ->where('id', '<>', $book->id)
                    ->exists()) {
                    $correctSlug = $baseSlug.'-'.$suffix;
                    $suffix++;
                }

                DB::table('sach')
                    ->where('id', $book->id)
                    ->update([
                        'tieu_de' => $correctTitle,
                        'duong_dan_tinh' => $correctSlug,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $titlePrefixes = [
            'Hạt Giống Cho Tâm Hồn - Tập ' => 'Hạt Giống Cho Tâm Hồn - Tập - Tập ',
            'Dạy Con Làm Giàu - Tập ' => 'Dạy Con Làm Giàu - Tập - Tập ',
            'Kính Vạn Hoa - Tập ' => 'Kính Vạn Hoa - Tập - Tập ',
        ];

        foreach ($titlePrefixes as $correctPrefix => $incorrectPrefix) {
            $books = DB::table('sach')
                ->where('tieu_de', 'like', $correctPrefix.'%')
                ->get(['id', 'tieu_de']);

            foreach ($books as $book) {
                $incorrectTitle = $incorrectPrefix.substr($book->tieu_de, strlen($correctPrefix));

                DB::table('sach')
                    ->where('id', $book->id)
                    ->update(['tieu_de' => $incorrectTitle]);
            }
        }
    }
};
