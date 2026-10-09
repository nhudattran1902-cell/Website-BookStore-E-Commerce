<?php

namespace Database\Seeders;

use App\Models\KhoHang;
use App\Models\NguoiDung;
use App\Models\NhaXuatBan;
use App\Models\Sach;
use App\Models\TacGia;
use App\Models\TheLoai;
use App\Models\TrangSach;
use App\Models\VaiTro;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AllTableSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo Vai trò (Roles)
        $roleAdmin = VaiTro::firstOrCreate(['ten_vai_tro' => 'admin'], ['mo_ta' => 'Quản trị hệ thống']);
        $roleCustomer = VaiTro::firstOrCreate(['ten_vai_tro' => 'customer'], ['mo_ta' => 'Khách hàng mua sách']);
        VaiTro::firstOrCreate(['ten_vai_tro' => 'cskh'], ['mo_ta' => 'Nhân viên chăm sóc khách hàng']);
        VaiTro::firstOrCreate(['ten_vai_tro' => 'nhan_vien_kho'], ['mo_ta' => 'Nhân viên quản lý kho']);
        VaiTro::firstOrCreate(['ten_vai_tro' => 'ke_toan'], ['mo_ta' => 'Nhân viên kế toán']);

        // 2. Tạo Tài khoản Người dùng (Admin & Customer)
        $admin = NguoiDung::firstOrCreate(
            ['email' => 'nhudattran1902@gmail.com'],
            [
                'ho_ten' => 'Administrator',
                'mat_khau' => Hash::make('12345678'),
                'so_dien_thoai' => '0901234567',
            ]
        );
        DB::table('vai_tro_nguoi_dung')->updateOrInsert([
            'id_nguoi_dung' => $admin->id,
            'id_vai_tro' => $roleAdmin->id,
        ]);

        $user = NguoiDung::firstOrCreate(
            ['email' => 'khachhang@gmail.com'],
            [
                'ho_ten' => 'Nguyễn Văn A',
                'mat_khau' => Hash::make('12345678'),
                'so_dien_thoai' => '0987654321',
            ]
        );
        DB::table('vai_tro_nguoi_dung')->updateOrInsert([
            'id_nguoi_dung' => $user->id,
            'id_vai_tro' => $roleCustomer->id,
        ]);

        // 3. Tạo Thể loại (Categories)
        $tlVanHoc = TheLoai::firstOrCreate(['duong_dan_tinh' => 'van-hoc'], ['ten_the_loai' => 'Văn học', 'mo_ta' => 'Tiểu thuyết, truyện ngắn trong và ngoài nước']);
        $tlKinhTe = TheLoai::firstOrCreate(['duong_dan_tinh' => 'kinh-te'], ['ten_the_loai' => 'Kinh tế', 'mo_ta' => 'Sách quản trị, đầu tư và khởi nghiệp']);
        $tlKyNang = TheLoai::firstOrCreate(['duong_dan_tinh' => 'ky-nang-song'], ['ten_the_loai' => 'Kỹ năng sống', 'mo_ta' => 'Sách phát triển bản thân']);

        // 4. Tạo Tác giả (Authors)
        $tg1 = TacGia::firstOrCreate(['ten_tac_gia' => 'Dale Carnegie'], ['tieu_su' => 'Tác giả nổi tiếng người Mỹ']);
        $tg2 = TacGia::firstOrCreate(['ten_tac_gia' => 'Nguyễn Nhật Ánh'], ['tieu_su' => 'Nhà văn thanh thiếu niên hàng đầu Việt Nam']);

        // 5. Tạo Nhà xuất bản (Publishers)
        $nxb1 = NhaXuatBan::firstOrCreate(['ten_nxb' => 'NXB Trẻ'], ['dia_chi' => 'TP. Hồ Chí Minh', 'email' => 'contact@nxbtre.com.vn']);
        $nxb2 = NhaXuatBan::firstOrCreate(['ten_nxb' => 'NXB Tổng Hợp'], ['dia_chi' => 'TP. Hồ Chí Minh', 'email' => 'info@nxbth.com.vn']);

        // 6. Tạo Sách (Books)
        $sach1 = Sach::firstOrCreate(
            ['duong_dan_tinh' => 'dac-nhan-tam'],
            [
                'id_the_loai' => $tlKyNang->id,
                'id_nha_xuat_ban' => $nxb1->id,
                'tieu_de' => 'Đắc Nhân Tâm',
                'mo_ta' => 'Cuốn sách nghệ thuật ứng xử nổi tiếng nhất thế giới.',
                'gia_ban' => 120000,
                'nam_xuat_ban' => 2023,
                'dang_hoat_dong' => 1,
            ]
        );

        $sach2 = Sach::firstOrCreate(
            ['duong_dan_tinh' => 'cho-toi-xin-mot-ve-di-tuoi-tho'],
            [
                'id_the_loai' => $tlVanHoc->id,
                'id_nha_xuat_ban' => $nxb2->id,
                'tieu_de' => 'Cho Tôi Xin Một Vé Đi Tuổi Thơ',
                'mo_ta' => 'Tác phẩm văn học hồn nhiên, đầy ký ức tuổi thơ.',
                'gia_ban' => 85000,
                'nam_xuat_ban' => 2022,
                'dang_hoat_dong' => 1,
            ]
        );

        // 7. Gán Tác giả cho Sách (bảng sach_tac_gia)
        DB::table('sach_tac_gia')->updateOrInsert(['id_sach' => $sach1->id, 'id_tac_gia' => $tg1->id]);
        DB::table('sach_tac_gia')->updateOrInsert(['id_sach' => $sach2->id, 'id_tac_gia' => $tg2->id]);

        // 8. Khởi tạo Kho hàng (Inventory)
        KhoHang::updateOrCreate(['id_sach' => $sach1->id], ['so_luong_ton' => 100, 'so_luong_dat_truoc' => 0]);
        KhoHang::updateOrCreate(['id_sach' => $sach2->id], ['so_luong_ton' => 50, 'so_luong_dat_truoc' => 0]);

        // 9. Khởi tạo Trang sách Đọc thử (Cho tính năng "Đọc ngay")
        TrangSach::firstOrCreate(
            ['id_sach' => $sach1->id, 'so_trang' => 1],
            ['duong_dan_anh' => 'sample/dac-nhan-tam-trang-1.jpg', 'cho_phep_doc_thu' => 1]
        );
        TrangSach::firstOrCreate(
            ['id_sach' => $sach1->id, 'so_trang' => 2],
            ['duong_dan_anh' => 'sample/dac-nhan-tam-trang-2.jpg', 'cho_phep_doc_thu' => 1]
        );
    }
}
