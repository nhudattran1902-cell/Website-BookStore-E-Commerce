<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TheLoai;

class TheLoaiSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'ten_the_loai' => 'Văn học',
                'duong_dan_tinh' => 'van-hoc',
                'mo_ta' => 'Sách văn học trong và ngoài nước'
            ],
            [
                'ten_the_loai' => 'Kinh tế',
                'duong_dan_tinh' => 'kinh-te',
                'mo_ta' => 'Sách quản trị và tài chính'
            ],
            [
                'ten_the_loai' => 'Kỹ năng sống',
                'duong_dan_tinh' => 'ky-nang-song',
                'mo_ta' => 'Sách phát triển bản thân'
            ],
        ];

        foreach ($categories as $cat) {
            // Tìm theo duong_dan_tinh, nếu chưa có mới tạo mới
            TheLoai::firstOrCreate(
                ['duong_dan_tinh' => $cat['duong_dan_tinh']],
                [
                    'ten_the_loai' => $cat['ten_the_loai'],
                    'mo_ta' => $cat['mo_ta']
                ]
            );
        }
    }
}
