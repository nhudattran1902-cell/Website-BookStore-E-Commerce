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

class MangaComicSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Thể loại Manga / Comic
        $theLoai = TheLoai::firstOrCreate(
            ['ten_the_loai' => 'Manga - Comic'],
            [
                'duong_dan_tinh' => 'manga-comic',
                'mo_ta' => 'Truyện tranh Nhật Bản và thế giới',
            ]
        );

        // 2. Tác giả & Nhà xuất bản
        $fujiko = TacGia::firstOrCreate(
            ['ten_tac_gia' => 'Fujiko F Fujio'],
            ['tieu_su' => 'Tác giả huyền thoại của bộ truyện Doraemon.']
        );

        $gotouge = TacGia::firstOrCreate(
            ['ten_tac_gia' => 'Koyoharu Gotouge'],
            ['tieu_su' => 'Tác giả của bộ truyện Kimetsu no Yaiba (Thanh Gươm Diệt Quỷ).']
        );

        $nxbKimDong = NhaXuatBan::firstOrCreate(
            ['ten_nxb' => 'NXB Kim Đồng'],
            [
                'dia_chi' => 'Hà Nội',
                'email' => 'contact@nxbkimdong.com.vn',
            ]
        );

        // 3. Danh sách 10 tập Doraemon
        $doraemonList = [
            ['tap' => 1, 'gia' => 25000, 'img' => 'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=500'],
            ['tap' => 2, 'gia' => 25000, 'img' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=500'],
            ['tap' => 3, 'gia' => 25000, 'img' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=500'],
            ['tap' => 4, 'gia' => 25000, 'img' => 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=500'],
            ['tap' => 5, 'gia' => 25000, 'img' => 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=500'],
            ['tap' => 6, 'gia' => 25000, 'img' => 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=500'],
            ['tap' => 7, 'gia' => 25000, 'img' => 'https://images.unsplash.com/photo-1531346878377-a5be20888e57?w=500'],
            ['tap' => 8, 'gia' => 25000, 'img' => 'https://images.unsplash.com/photo-1495640388908-05fa85288e61?w=500'],
            ['tap' => 9, 'gia' => 25000, 'img' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=500'],
            ['tap' => 10, 'gia' => 25000, 'img' => 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=500'],
        ];

        foreach ($doraemonList as $item) {
            $tieuDe = 'Doraemon - Tập '.$item['tap'];
            $sach = Sach::updateOrCreate(
                ['tieu_de' => $tieuDe],
                [
                    'duong_dan_tinh' => Str::slug($tieuDe),
                    'id_the_loai' => $theLoai->id,
                    'id_nha_xuat_ban' => $nxbKimDong->id,
                    'gia_ban' => $item['gia'],
                    'mo_ta' => 'Chú mèo máy Doraemon đến từ thế kỷ 22 cùng các bảo bối kỳ diệu đồng hành cùng Nobita. Tập '.$item['tap'],
                    'anh_bia' => $item['img'],
                    'ma_isbn' => '9786042'.sprintf('%05d', $item['tap']),
                    'nam_xuat_ban' => 2026,
                    'dang_hoat_dong' => true,
                ]
            );

            $sach->tacGias()->syncWithoutDetaching([$fujiko->id]);

            KhoHang::updateOrCreate(
                ['id_sach' => $sach->id],
                ['so_luong_ton' => rand(20, 100)]
            );

            for ($p = 1; $p <= 3; $p++) {
                TrangSach::updateOrCreate(
                    ['id_sach' => $sach->id, 'so_trang' => $p],
                    ['duong_dan_anh' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800']
                );
            }
        }

        // 4. Danh sách 10 tập Kimetsu no Yaiba
        $kimetsuList = [
            ['tap' => 1, 'gia' => 30000, 'img' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=500'],
            ['tap' => 2, 'gia' => 30000, 'img' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=500'],
            ['tap' => 3, 'gia' => 30000, 'img' => 'https://images.unsplash.com/photo-1563089145-599997674d42?w=500'],
            ['tap' => 4, 'gia' => 30000, 'img' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=500'],
            ['tap' => 5, 'gia' => 30000, 'img' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=500'],
            ['tap' => 6, 'gia' => 30000, 'img' => 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?w=500'],
            ['tap' => 7, 'gia' => 30000, 'img' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=500'],
            ['tap' => 8, 'gia' => 30000, 'img' => 'https://images.unsplash.com/photo-1550684848-fac1c5b4e853?w=500'],
            ['tap' => 9, 'gia' => 30000, 'img' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=500'],
            ['tap' => 10, 'gia' => 30000, 'img' => 'https://images.unsplash.com/photo-1569003339405-ea396a5a8a90?w=500'],
        ];

        foreach ($kimetsuList as $item) {
            $tieuDe = 'Thanh Gươm Diệt Quỷ - Tập '.$item['tap'];
            $sach = Sach::updateOrCreate(
                ['tieu_de' => $tieuDe],
                [
                    'duong_dan_tinh' => Str::slug($tieuDe),
                    'id_the_loai' => $theLoai->id,
                    'id_nha_xuat_ban' => $nxbKimDong->id,
                    'gia_ban' => $item['gia'],
                    'mo_ta' => 'Hành trình diệt quỷ tìm lại làm người cho em gái Nezuko của Tanjiro Kamado. Tập '.$item['tap'],
                    'anh_bia' => $item['img'],
                    'ma_isbn' => '9786043'.sprintf('%05d', $item['tap']),
                    'nam_xuat_ban' => 2026,
                    'dang_hoat_dong' => true,
                ]
            );

            $sach->tacGias()->syncWithoutDetaching([$gotouge->id]);

            KhoHang::updateOrCreate(
                ['id_sach' => $sach->id],
                ['so_luong_ton' => rand(15, 80)]
            );

            for ($p = 1; $p <= 3; $p++) {
                TrangSach::updateOrCreate(
                    ['id_sach' => $sach->id, 'so_trang' => $p],
                    ['duong_dan_anh' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=800']
                );
            }
        }
    }
}
