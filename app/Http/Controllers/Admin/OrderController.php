<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Hiển thị danh sách đơn hàng
    public function index(Request $request): View
    {
        $query = DonHang::with(['nguoiDung'])->orderBy('id', 'desc');

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $orders = $query->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    // Xem chi tiết đơn hàng (Đã sửa tên biến truyền sang view thành $order)
    public function show(int $id): View
    {
        $order = DonHang::with(['nguoiDung', 'chiTietDonHang.sach', 'thanhToan'])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    // Cập nhật trạng thái giao hàng
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'trang_thai' => 'required|in:cho_xu_ly,dang_xu_ly,dang_giao,hoan_thanh,da_huy',
        ]);

        $donHang = DonHang::findOrFail($id);
        $donHang->update(['trang_thai' => $request->trang_thai]);

        return redirect()->back()->with('success', 'Cập nhật trạng thái đơn hàng thành công!');
    }
}