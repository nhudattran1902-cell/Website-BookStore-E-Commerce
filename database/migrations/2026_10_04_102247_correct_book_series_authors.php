<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            foreach ($this->authorMappings() as $titlePrefix => $authorName) {
                $authorId = $this->findOrCreateAuthor($authorName);
                $bookIds = DB::table('sach')
                    ->where('tieu_de', 'like', $titlePrefix.'%')
                    ->pluck('id');

                if ($bookIds->isEmpty()) {
                    continue;
                }

                DB::table('sach_tac_gia')->whereIn('id_sach', $bookIds)->delete();
                DB::table('sach_tac_gia')->insert(
                    $bookIds->map(fn (int $bookId): array => [
                        'id_sach' => $bookId,
                        'id_tac_gia' => $authorId,
                    ])->all()
                );
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $fujikoId = $this->findOrCreateAuthor('Fujiko F Fujio');

            foreach (array_keys($this->authorMappings()) as $titlePrefix) {
                $bookIds = DB::table('sach')
                    ->where('tieu_de', 'like', $titlePrefix.'%')
                    ->pluck('id');

                if ($bookIds->isEmpty()) {
                    continue;
                }

                DB::table('sach_tac_gia')->whereIn('id_sach', $bookIds)->delete();
                DB::table('sach_tac_gia')->insert(
                    $bookIds->map(fn (int $bookId): array => [
                        'id_sach' => $bookId,
                        'id_tac_gia' => $fujikoId,
                    ])->all()
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    private function authorMappings(): array
    {
        return [
            'Cẩm Nang Ôn Thi THPT Quốc Gia - Môn' => 'Nhiều tác giả',
            'Hạt Giống Cho Tâm Hồn' => 'Nhiều tác giả',
            'Chúa Tể Những Chiếc Nhẫn' => 'J. R. R. Tolkien',
            'Dạy Con Làm Giàu' => 'Robert T. Kiyosaki',
            'Kính Vạn Hoa' => 'Nguyễn Nhật Ánh',
            'Tâm Lý Học Chữa Lành' => 'Nhiều tác giả',
            'Khám Phá Vũ Trụ' => 'Nhiều tác giả',
            'Lược Sử Loài Người' => 'Yuval Noah Harari',
        ];
    }

    private function findOrCreateAuthor(string $authorName): int
    {
        $authorId = DB::table('tac_gia')
            ->where('ten_tac_gia', $authorName)
            ->value('id');

        if ($authorId !== null) {
            return (int) $authorId;
        }

        return (int) DB::table('tac_gia')->insertGetId([
            'ten_tac_gia' => $authorName,
            'ngay_tao' => now(),
            'ngay_cap_nhat' => now(),
        ]);
    }
};
