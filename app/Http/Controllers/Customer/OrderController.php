<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // Hiển thị danh sách lịch sử đơn hàng của user đang đăng nhập
    public function index()
    {
        // Thay latest() bằng orderBy('id', 'desc') để tránh lỗi tìm cột created_at trong bảng don_hang
        $danhSachDonHang = DonHang::where('id_nguoi_dung', Auth::id())
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('customer.orders.index', compact('danhSachDonHang'));
    }

    // Xem chi tiết một đơn hàng cụ thể
    public function show($id)
    {
        // Query đơn hàng kèm các mối quan hệ chi tiết và thanh toán
        $order = DonHang::with(['chiTietDonHang.sach', 'thanhToan'])
            ->where('id_nguoi_dung', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        // Truyền biến $order để khớp 100% với file resources/views/customer/orders/show.blade.php
        return view('customer.orders.show', compact('order'));
    }
}
