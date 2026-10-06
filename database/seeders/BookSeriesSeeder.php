<?php

namespace Database\Seeders;

use App\Models\KhoHang;
use App\Models\NhaXuatBan;
use App\Models\Sach;
use App\Models\TacGia;
use App\Models\TheLoai;
use App\Models\TrangSach;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookSeriesSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------------------------------------
        // 1. TẠO TÁC GIẢ & NHÀ XUẤT BẢN CƠ BẢN
        // ----------------------------------------------------
        $fujiko = TacGia::firstOrCreate(
            ['ten_tac_gia' => 'Fujiko F Fujio'],
            ['tieu_su' => 'Tác giả huyền thoại của bộ truyện Doraemon.']
        );

        $nhieuTacGia = TacGia::firstOrCreate(
            ['ten_tac_gia' => 'Nhiều tác giả'],
            ['tieu_su' => 'Ấn phẩm được biên soạn bởi nhiều tác giả.']
        );

        $tolkien = TacGia::firstOrCreate(
            ['ten_tac_gia' => 'J. R. R. Tolkien'],
            ['tieu_su' => 'Nhà văn người Anh, tác giả bộ Chúa Tể Những Chiếc Nhẫn.']
        );

        $kiyosaki = TacGia::firstOrCreate(
            ['ten_tac_gia' => 'Robert T. Kiyosaki'],
            ['tieu_su' => 'Tác giả và doanh nhân người Mỹ, nổi tiếng với các sách giáo dục tài chính.']
        );

        $nguyenNhatAnh = TacGia::firstOrCreate(
            ['ten_tac_gia' => 'Nguyễn Nhật Ánh'],
            ['tieu_su' => 'Nhà văn Việt Nam nổi tiếng với các tác phẩm dành cho tuổi mới lớn.']
        );

        $yuvalNoahHarari = TacGia::firstOrCreate(
            ['ten_tac_gia' => 'Yuval Noah Harari'],
            ['tieu_su' => 'Nhà sử học và tác giả của các tác phẩm về lịch sử loài người.']
        );

        $nxbKimDong = NhaXuatBan::firstOrCreate(
            ['ten_nxb' => 'NXB Kim Đồng'],
            ['dia_chi' => 'Hà Nội']
        );

        $nxbTre = NhaXuatBan::firstOrCreate(
            ['ten_nxb' => 'NXB Trẻ'],
            ['dia_chi' => 'TP. Hồ Chí Minh']
        );

        $nxbGiaoDuc = NhaXuatBan::firstOrCreate(
            ['ten_nxb' => 'NXB Giáo Dục Việt Nam'],
            ['dia_chi' => 'Hà Nội']
        );

        // ----------------------------------------------------
        // 2. KHỞI TẠO 10 CHỦ ĐỀ VÀ CÁC SERIES BÊN TRONG
        // ----------------------------------------------------
        $structure = [
            // 主題 1: Truyện Tranh Thiếu Nhi (Short)
            [
                'cat_name' => 'Manga - Truyện Ngắn Thiếu Nhi',
                'cat_slug' => 'manga-truyen-ngan-thieu-nhi',
                'series' => [
                    [
                        'prefix' => 'Doraemon Truyện Ngắn',
                        'count' => 5,
                        'price' => 25000,
                        'img' => 'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=500',
                        'desc' => 'Chú mèo máy Doraemon đồng hành cùng Nobita trong cuộc sống hàng ngày.',
                        'nxb_id' => $nxbKimDong->id,
                        'author' => $fujiko->id,
                    ],
                ],
            ],
            // 主題 2: Truyện Tranh Phiêu Lưu Dài Tập (Long)
            [
                'cat_name' => 'Manga - Truyện Dài Phiêu Lưu',
                'cat_slug' => 'manga-truyen-dai-phieu-luu',
                'series' => [
                    [
                        'prefix' => 'Doraemon Truyện Dài - Tập',
                        'titles' => [
                            1 => 'Thăm Hành Tinh Của Những Chú Chó',
                            2 => 'Chú Khủng Long Của Nobita',
                            3 => 'Bí Mật Hành Tinh Tím',
                        ],
                        'price' => 35000,
                        'img' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500',
                        'desc' => 'Cuộc phiêu lưu kỳ thú của nhóm bạn Doraemon đến những thế giới xa xôi.',
                        'nxb_id' => $nxbKimDong->id,
                        'author' => $fujiko->id,
                    ],
                ],
            ],
            // 主題 3: Sách Học Tập - Luyện Thi
            [
                'cat_name' => 'Học Tập & Luyện Thi',
                'cat_slug' => 'hoc-tap-luyen-thi',
                'series' => [
                    [
                        'prefix' => 'Cẩm Nang Ôn Thi THPT Quốc Gia - Môn',
                        'titles' => [1 => 'Toán', 2 => 'Văn', 3 => 'Tiếng Anh'],
                        'price' => 120000,
                        'img' => 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=500',
                        'desc' => 'Bộ sách kiến thức trọng tâm và đề thi thử đại học chuẩn cấu hình.',
                        'nxb_id' => $nxbGiaoDuc->id,
                        'author' => $nhieuTacGia->id,
                    ],
                ],
            ],
            // 主題 4: Phát Triển Kỹ Năng Sống
            [
                'cat_name' => 'Kỹ Năng Sống & Tư Duy',
                'cat_slug' => 'ky-nang-song-tu-duy',
                'series' => [
                    [
                        'prefix' => 'Hạt Giống Cho Tâm Hồn - Tập',
                        'count' => 3,
                        'price' => 75000,
                        'img' => 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=500',
                        'desc' => 'Tuyển tập những câu chuyện giàu ý nghĩa nhân văn và nghị lực sống.',
                        'nxb_id' => $nxbTre->id,
                        'author' => $nhieuTacGia->id,
                    ],
                ],
            ],
            // 主題 5: Văn Học - Tiểu Thuyết Kinh Điển
            [
                'cat_name' => 'Văn Học Kinh Điển',
                'cat_slug' => 'van-hoc-kinh-dien',
                'series' => [
                    [
                        'prefix' => 'Chúa Tể Những Chiếc Nhẫn - Phần',
                        'titles' => [
                            1 => 'Đoàn Hộ Nhẫn',
                            2 => 'Hai Tháp',
                            3 => 'Nhà Vua Trở Về',
                        ],
                        'price' => 165000,
                        'img' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=500',
                        'desc' => 'Bộ tiểu thuyết viễn tưởng kinh điển về cuộc chiến bảo vệ Trung Địa.',
                        'nxb_id' => $nxbTre->id,
                        'author' => $tolkien->id,
                    ],
                ],
            ],
            // 主題 6: Kinh Tế - Tài Chính
            [
                'cat_name' => 'Kinh Tế & Quản Trị',
                'cat_slug' => 'kinh-te-quan-tri',
                'series' => [
                    [
                        'prefix' => 'Dạy Con Làm Giàu - Tập',
                        'count' => 3,
                        'price' => 110000,
                        'img' => 'https://images.unsplash.com/photo-1550684848-fac1c5b4e853?w=500',
                        'desc' => 'Bộ sách tư duy tài chính cá nhân nổi tiếng của Robert Kiyosaki.',
                        'nxb_id' => $nxbTre->id,
                        'author' => $kiyosaki->id,
                    ],
                ],
            ],
            // 主題 7: Thiếu Nhi & Tuổi Mới Lớn
            [
                'cat_name' => 'Tuổi Mới Lớn',
                'cat_slug' => 'tuoi-moi-lon',
                'series' => [
                    [
                        'prefix' => 'Kính Vạn Hoa - Tập',
                        'count' => 3,
                        'price' => 45000,
                        'img' => 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=500',
                        'desc' => 'Những câu chuyện học trò dí dỏm, thông minh của Nguyễn Nhật Ánh.',
                        'nxb_id' => $nxbKimDong->id,
                        'author' => $nguyenNhatAnh->id,
                    ],
                ],
            ],
            // 主題 8: Tâm Lý Học
            [
                'cat_name' => 'Tâm Lý Học UD',
                'cat_slug' => 'tam-ly-hoc-ung-dung',
                'series' => [
                    [
                        'prefix' => 'Tâm Lý Học Chữa Lành - Bộ',
                        'titles' => [1 => 'Thấu Hiểu Bản Thân', 2 => 'Chữa Lành Cảm Xúc'],
                        'price' => 99000,
                        'img' => 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=500',
                        'desc' => 'Phương pháp nhận biết và điều hòa tâm lý trong đời sống hiện đại.',
                        'nxb_id' => $nxbTre->id,
                        'author' => $nhieuTacGia->id,
                    ],
                ],
            ],
            // 主題 9: Khoa Học - Vũ Trụ
            [
                'cat_name' => 'Khoa Học & Tự Nhiên',
                'cat_slug' => 'khoa-hoc-tu-nhien',
                'series' => [
                    [
                        'prefix' => 'Khám Phá Vũ Trụ - Tập',
                        'titles' => [1 => 'Hệ Mặt Trời', 2 => 'Các Lỗ Đen Kỳ Bí'],
                        'price' => 135000,
                        'img' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=500',
                        'desc' => 'Tri thức thiên văn học minh họa trực quan sinh động.',
                        'nxb_id' => $nxbGiaoDuc->id,
                        'author' => $nhieuTacGia->id,
                    ],
                ],
            ],
            // 主題 10: Lịch Sử & Văn Hoá
            [
                'cat_name' => 'Lịch Sử & Văn Hoá',
                'cat_slug' => 'lich-su-van-hoa',
                'series' => [
                    [
                        'prefix' => 'Lược Sử Loài Người - Phần',
                        'titles' => [1 => 'Cách Mạng Nhận Thức', 2 => 'Cách Mạng Nông Nghiệp'],
                        'price' => 180000,
                        'img' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=500',
                        'desc' => 'Hành trình phát triển và tiến hóa của loài người qua các kỷ nguyên.',
                        'nxb_id' => $nxbTre->id,
                        'author' => $yuvalNoahHarari->id,
                    ],
                ],
            ],
        ];

        // ----------------------------------------------------
        // 3. THỰC THI THÊM VÀO DATABASE
        // ----------------------------------------------------
        foreach ($structure as $item) {
            // Tạo Thể Loại (Chủ đề)
            $theLoai = TheLoai::firstOrCreate(
                ['ten_the_loai' => $item['cat_name']],
                [
                    'duong_dan_tinh' => $item['cat_slug'],
                    'mo_ta' => 'Chủ đề: '.$item['cat_name'],
                ]
            );

            // Duyệt qua từng Series trong Chủ đề
            foreach ($item['series'] as $ser) {
                if (isset($ser['count'])) {
                    // Dạng series số thứ tự: Tập 1, Tập 2,...
                    for ($i = 1; $i <= $ser['count']; $i++) {
                        $tieuDe = "{$ser['prefix']} - Tập {$i}";
                        $this->createBookItem($tieuDe, $theLoai->id, $ser);
                    }
                } elseif (isset($ser['titles'])) {
                    // Dạng series có tên tập cụ thể
                    foreach ($ser['titles'] as $idx => $subTitle) {
                        $tieuDe = "{$ser['prefix']} {$idx}: {$subTitle}";
                        $this->createBookItem($tieuDe, $theLoai->id, $ser);
                    }
                }
            }
        }
    }

    /**
     * Hàm phụ hỗ trợ khởi tạo Sách, Kho hàng và Trang đọc thử
     */
    private function createBookItem(string $tieuDe, int $theLoaiId, array $serInfo): void
    {
        $sach = Sach::updateOrCreate(
            ['tieu_de' => $tieuDe],
            [
                'duong_dan_tinh' => Str::slug($tieuDe),
                'id_the_loai' => $theLoaiId,
                'id_nha_xuat_ban' => $serInfo['nxb_id'],
                'gia_ban' => $serInfo['price'],
                'mo_ta' => $serInfo['desc'],
                'anh_bia' => $serInfo['img'],
                'ma_isbn' => '978'.rand(100000000, 999999999),
                'nam_xuat_ban' => 2026,
                'dang_hoat_dong' => true,
            ]
        );

        // Gắn quan hệ Tác giả vào bảng Pivot
        if (method_exists($sach, 'tacGias')) {
            $sach->tacGias()->sync([$serInfo['author']]);
        } elseif (method_exists($sach, 'tacGia')) {
            $sach->tacGia()->sync([$serInfo['author']]);
        }

        // Tạo bản ghi tồn kho
        KhoHang::updateOrCreate(
            ['id_sach' => $sach->id],
            ['so_luong_ton' => rand(30, 100)]
        );

        // Tạo 3 trang đọc thử mẫu
        for ($p = 1; $p <= 3; $p++) {
            TrangSach::updateOrCreate(
                ['id_sach' => $sach->id, 'so_trang' => $p],
                [
                    'duong_dan_anh' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800',
                    'cho_phep_doc_thu' => true,
                ]
            );
        }
    }
}
