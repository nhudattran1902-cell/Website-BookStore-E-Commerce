<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ChiTietGioHang;
use App\Models\GioHang;
use App\Models\KhoHang;
use App\Models\Sach;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
    public function addToCart(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'id_sach' => 'required|exists:sach,id',
            'so_luong' => 'required|integer|min:1',
        ]);

        $userId = Auth::id();
        $requestedQuantity = (int) $request->input('so_luong');

        [$sach, $gioHang] = DB::transaction(function () use ($request, $userId, $requestedQuantity): array {
            $sach = Sach::where('id', $request->id_sach)
                ->where('dang_hoat_dong', 1)
                ->firstOrFail();
            $khoHang = KhoHang::where('id_sach', $sach->id)->lockForUpdate()->first();
            $availableQuantity = (int) ($khoHang?->so_luong_kha_dung ?? 0);

            $gioHang = GioHang::where('id_nguoi_dung', $userId)->first();
            $item = $gioHang
                ? ChiTietGioHang::where('id_gio_hang', $gioHang->id)
                    ->where('id_sach', $sach->id)
                    ->first()
                : null;

            $quantityInCart = (int) ($item->so_luong ?? 0);
            $remainingQuantity = max(0, $availableQuantity - $quantityInCart);

            if ($requestedQuantity > $remainingQuantity) {
                $message = $availableQuantity === 0
                    ? 'Sách hiện đã hết hàng.'
                    : 'Chỉ còn '.$remainingQuantity.' cuốn có thể thêm vào giỏ.';

                throw ValidationException::withMessages(['so_luong' => $message]);
            }

            $gioHang ??= GioHang::create(['id_nguoi_dung' => $userId]);

            if ($item) {
                $item->increment('so_luong', $requestedQuantity);
            } else {
                ChiTietGioHang::create([
                    'id_gio_hang' => $gioHang->id,
                    'id_sach' => $sach->id,
                    'so_luong' => $requestedQuantity,
                ]);
            }

            return [$sach, $gioHang];
        });

        if ($request->wantsJson() || $request->ajax()) {
            $cartCount = ChiTietGioHang::where('id_gio_hang', $gioHang->id)->sum('so_luong');

            return response()->json([
                'book_name' => $sach->tieu_de,
                'cart_count' => (int) $cartCount,
                'message' => 'Đã thêm '.$sach->tieu_de.' vào giỏ hàng!',
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
            'id' => 'required|exists:chi_tiet_gio_hang,id',
            'so_luong' => 'required|integer|min:1',
        ]);

        $requestedQuantity = (int) $request->input('so_luong');
        $userId = Auth::id();

        DB::transaction(function () use ($itemId, $requestedQuantity, $userId): void {
            $item = ChiTietGioHang::with('gioHang')->lockForUpdate()->findOrFail($itemId);

            if ($item->gioHang->id_nguoi_dung !== $userId) {
                abort(403, 'Bạn không có quyền chỉnh sửa giỏ hàng này.');
            }

            $khoHang = KhoHang::where('id_sach', $item->id_sach)->lockForUpdate()->first();
            $availableQuantity = (int) ($khoHang?->so_luong_kha_dung ?? 0);

            if ($requestedQuantity > $availableQuantity) {
                $message = $availableQuantity === 0
                    ? 'Sách hiện đã hết hàng.'
                    : 'Chỉ còn '.$availableQuantity.' cuốn có thể mua.';

                throw ValidationException::withMessages(['so_luong' => $message]);
            }

            $item->update(['so_luong' => $requestedQuantity]);
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Đã cập nhật số lượng thành công!',
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
