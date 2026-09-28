<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Hiển thị danh sách đơn hàng
    public function index(Request $request)
    {
        $query = DonHang::with(['nguoiDung'])->orderBy('id', 'desc');

        // Lọc theo trạng thái đơn hàng nếu người dùng chọn
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $danhSachDonHang = $query->paginate(10);

        return view('admin.orders.index', compact('danhSachDonHang'));
    }

    // Xem chi tiết đơn hàng
    public function show($id)
    {
        $donHang = DonHang::with(['nguoiDung', 'chiTietDonHang.sach', 'thanhToan'])->findOrFail($id);
        
        return view('admin.orders.show', compact('donHang'));
    }

    // Cập nhật trạng thái giao hàng
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'trang_thai' => 'required|in:cho_xu_ly,dang_xu_ly,dang_giao,hoan_thanh,da_huy'
        ]);

        $donHang = DonHang::findOrFail($id);
        $donHang->update(['trang_thai' => $request->trang_thai]);

        return redirect()->back()->with('success', 'Cập nhật trạng thái đơn hàng thành công!');
    }
}