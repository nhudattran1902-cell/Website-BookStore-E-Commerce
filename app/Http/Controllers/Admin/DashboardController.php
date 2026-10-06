<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\KhoHang;
use App\Models\NguoiDung;
use App\Models\Sach;
use App\Models\TinNhanChat;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Hiển thị trang Dashboard Quản trị BOOK & BOX
     */
    public function index()
    {
        // 1. Tính tổng doanh thu từ các đơn hàng có trạng thái 'hoan_thanh'
        $tongDoanhThu = DonHang::where('trang_thai', 'hoan_thanh')->sum('thanh_tien');

        // 2. Đếm số đơn hàng mới cần xử lý (trạng thái 'cho_xu_ly')
        $donHangMoi = DonHang::where('trang_thai', 'cho_xu_ly')->count();

        // 3. Tính tổng số lượng sách tồn trong kho (từ bảng kho_hang)
        $tongSachTonKho = KhoHang::sum('so_luong_ton');

        // 4. Lấy 5 đơn hàng giao dịch gần đây nhất kèm thông tin người đặt (nối bảng nguoi_dung)
        $donHangGanDay = DonHang::with('nguoiDung')
            ->latest('ngay_tao')
            ->take(5)
            ->get();

        // 5. Thống kê tổng số đầu sách và số người dùng đăng ký
        $tongDauSach = Sach::count();
        $tongKhachHang = NguoiDung::count();

        // 6. Tin nhắn khách mới nhất để hỗ trợ trực tiếp từ Dashboard
        $hasChatTable = Schema::hasTable('tin_nhan_chat');

        $tinNhanHoTroMoi = $hasChatTable
            ? TinNhanChat::with('nguoiDung')
                ->kenh(TinNhanChat::KENH_CSKH)
                ->latest('ngay_tao')
                ->limit(5)
                ->get()
            : collect();

        $tinHoTroChuaDoc = $hasChatTable
            ? TinNhanChat::kenh(TinNhanChat::KENH_CSKH)
                ->where('nguoi_gui', 'khach_hang')
                ->where('da_doc', false)
                ->count()
            : 0;

        // Truyền tất cả dữ liệu sang view admin.dashboard
        return view('admin.dashboard', compact(
            'tongDoanhThu',
            'donHangMoi',
            'tongSachTonKho',
            'donHangGanDay',
            'tongDauSach',
            'tongKhachHang',
            'tinNhanHoTroMoi',
            'tinHoTroChuaDoc'
        ));
    }
}
