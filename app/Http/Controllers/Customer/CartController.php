<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\GioHang;
use App\Models\ChiTietGioHang;
use App\Models\Sach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // 1. Hiển thị danh sách các sách trong giỏ
    public function index()
    {
        $userId = Auth::id();

        $gioHang = GioHang::with(['chiTietGioHang.sach'])
            ->where('id_nguoi_dung', $userId)
            ->first();

        $cartItems = $gioHang ? $gioHang->chiTietGioHang : collect();

        // Tính tổng tiền giỏ hàng
        $tongTien = $cartItems->sum(function ($item) {
            $gia = $item->sach->gia_khuyen_mai ?? $item->sach->gia_ban;
            return $gia * $item->so_luong;
        });

        return view('customer.cart', compact('cartItems', 'tongTien'));
    }

    // 2. Thêm sách vào giỏ
    public function addToCart(Request $request)
    {
        $request->validate([
            'id_sach'  => 'required|exists:sach,id',
            'so_luong' => 'required|integer|min:1'
        ]);

        $userId = Auth::id();

        // Kiểm tra sách có đang hoạt động không
        $sach = Sach::where('id', $request->id_sach)
            ->where('dang_hoat_dong', 1)
            ->firstOrFail();

        // Lấy hoặc tạo mới giỏ hàng cho user
        $gioHang = GioHang::firstOrCreate(['id_nguoi_dung' => $userId]);

        // Kiểm tra xem sách đã có trong chi tiết giỏ hàng chưa
        $item = ChiTietGioHang::where('id_gio_hang', $gioHang->id)
            ->where('id_sach', $request->id_sach)
            ->first();

        if ($item) {
            // Nếu đã có thì cộng dồn số lượng
            $item->increment('so_luong', $request->so_luong);
        } else {
            ChiTietGioHang::create([
                'id_gio_hang' => $gioHang->id,
                'id_sach'     => $request->id_sach,
                'so_luong'    => $request->so_luong
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Đã thêm sách vào giỏ hàng!');
    }

    // 3. Cập nhật số lượng sách trong giỏ (hỗ trợ cả URL parameter lẫn request body)
    public function updateCart(Request $request, $id = null)
    {
        $itemId = $id ?? $request->id;
        $request->merge(['id' => $itemId]);

        $request->validate([
            'id'       => 'required|exists:chi_tiet_gio_hang,id',
            'so_luong' => 'required|integer|min:1'
        ]);

        $item = ChiTietGioHang::findOrFail($itemId);

        // Kiểm tra quyền sở hữu: item phải thuộc giỏ hàng của user hiện tại
        $userId = Auth::id();
        if ($item->gioHang->id_nguoi_dung !== $userId) {
            abort(403, 'Bạn không có quyền chỉnh sửa giỏ hàng này.');
        }

        $item->update(['so_luong' => $request->so_luong]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Đã cập nhật số lượng thành công!'
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Đã cập nhật số lượng thành công!');
    }

    // 4. Xóa sách khỏi giỏ hàng
    public function destroy($id)
    {
        $item = ChiTietGioHang::findOrFail($id);

        // Kiểm tra quyền sở hữu: item phải thuộc giỏ hàng của user hiện tại
        $userId = Auth::id();
        if ($item->gioHang->id_nguoi_dung !== $userId) {
            abort(403, 'Bạn không có quyền xóa sản phẩm khỏi giỏ hàng này.');
        }

        $item->delete();

        return redirect()->back()->with('success', 'Đã xóa sách khỏi giỏ hàng!');
    }
}
