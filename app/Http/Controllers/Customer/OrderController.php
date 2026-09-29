<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách lịch sử đơn hàng của user đang đăng nhập
     */
    public function index(): View
    {
        // Dùng orderBy('id', 'desc') để sắp xếp đơn hàng mới nhất
        $donHangs = DonHang::with(['chiTietDonHang.sach', 'thanhToan'])
            ->where('id_nguoi_dung', Auth::id())
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Tạo alias $orders và $danhSachDonHang để khớp với mọi file Blade View
        $orders = $donHangs;
        $danhSachDonHang = $donHangs;

        return view('customer.orders.index', compact('donHangs', 'orders', 'danhSachDonHang'));
    }

    /**
     * Xem chi tiết một đơn hàng cụ thể
     */
    public function show(int $id): View
    {
        // Query đơn hàng kèm các mối quan hệ chi tiết và thanh toán
        $order = DonHang::with(['chiTietDonHang.sach', 'thanhToan', 'nguoiDung'])
            ->where('id_nguoi_dung', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $donHang = $order;

        return view('customer.orders.show', compact('order', 'donHang'));
    }
}