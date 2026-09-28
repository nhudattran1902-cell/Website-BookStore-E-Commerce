<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\GioHang;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\KhoHang;
use App\Models\ThanhToan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    // 1. Hiển thị trang Form thanh toán
    public function index()
    {
        $userId = Auth::id();

        $gioHang = GioHang::with(['chiTietGioHang.sach'])
            ->where('id_nguoi_dung', $userId)
            ->first();

        if (!$gioHang || $gioHang->chiTietGioHang->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống!');
        }

        $cartItems = $gioHang->chiTietGioHang;
        $tongTien  = $cartItems->sum(function ($item) {
            $gia = $item->sach->gia_khuyen_mai ?? $item->sach->gia_ban;
            return $gia * $item->so_luong;
        });

        return view('customer.checkout', compact('cartItems', 'tongTien'));
    }

    // 2. Xử lý Đặt hàng (Lưu thông tin vào CSDL)
    public function process(Request $request)
    {
        $request->validate([
            'ho_ten'                 => 'required|string|max:255',
            'so_dien_thoai'          => 'required|string|max:20',
            'dia_chi_giao_hang'      => 'required|string',
            'phuong_thuc_thanh_toan' => 'required|in:COD,MoMo,VNPay',
            'ghi_chu'                => 'nullable|string'
        ]);

        $userId  = Auth::id();
        $gioHang = GioHang::with(['chiTietGioHang.sach'])->where('id_nguoi_dung', $userId)->first();

        if (!$gioHang || $gioHang->chiTietGioHang->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $cartItems = $gioHang->chiTietGioHang;

        // === Kiểm tra tồn kho trước khi thanh toán ===
        foreach ($cartItems as $item) {
            if (!$item->sach || !$item->sach->dang_hoat_dong) {
                return redirect()->route('cart.index')
                    ->with('error', "Sách \"" . ($item->sach->tieu_de ?? 'không xác định') . "\" hiện không còn bán.");
            }

            $kho = KhoHang::where('id_sach', $item->id_sach)->first();
            if (!$kho || $kho->so_luong_ton < $item->so_luong) {
                $conLai = $kho ? $kho->so_luong_ton : 0;
                return redirect()->route('cart.index')
                    ->with('error', "Sách \"{$item->sach->tieu_de}\" chỉ còn {$conLai} cuốn trong kho.");
            }
        }

        $tongTien = $cartItems->sum(function ($item) {
            $gia = $item->sach->gia_khuyen_mai ?? $item->sach->gia_ban;
            return $gia * $item->so_luong;
        });

        DB::beginTransaction();
        try {
            // A. Tạo Đơn hàng mới
            $donHang = DonHang::create([
                'ma_don_hang'       => 'ORD-' . strtoupper(Str::random(8)),
                'id_nguoi_dung'     => $userId,
                'tong_tien'         => $tongTien,
                'so_tien_giam_gia'  => 0,
                'thanh_tien'        => $tongTien,
                'trang_thai'        => 'cho_xu_ly',
                'dia_chi_giao_hang' => $request->dia_chi_giao_hang
                    . ' (SĐT: ' . $request->so_dien_thoai
                    . ' - Người nhận: ' . $request->ho_ten . ')',
                'ghi_chu'           => $request->ghi_chu
            ]);

            // B. Lưu Chi tiết đơn hàng & Trừ kho (lockForUpdate)
            foreach ($cartItems as $item) {
                $donGia = $item->sach->gia_khuyen_mai ?? $item->sach->gia_ban;

                ChiTietDonHang::create([
                    'id_don_hang' => $donHang->id,
                    'id_sach'     => $item->id_sach,
                    'don_gia'     => $donGia,
                    'so_luong'    => $item->so_luong,
                    'thanh_tien'  => $donGia * $item->so_luong
                ]);

                // Trừ kho với lock để tránh race condition
                $kho = KhoHang::where('id_sach', $item->id_sach)->lockForUpdate()->first();
                $kho->decrement('so_luong_ton', $item->so_luong);
            }

            // C. Tạo bản ghi Thanh toán
            ThanhToan::create([
                'id_don_hang'            => $donHang->id,
                'phuong_thuc_thanh_toan' => $request->phuong_thuc_thanh_toan,
                'so_tien'                => $tongTien,
                'trang_thai'             => 'cho_thanh_toan'
            ]);

            // D. Xóa giỏ hàng sau khi đặt thành công
            $gioHang->chiTietGioHang()->delete();

            DB::commit();

            return redirect()->route('checkout.success', $donHang->ma_don_hang);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Lỗi xử lý đơn hàng: ' . $e->getMessage());
        }
    }

    // 3. Trang đặt hàng thành công
    public function success($maDonHang)
    {
        // Kiểm tra đơn hàng thuộc về user hiện tại
        $donHang = DonHang::where('ma_don_hang', $maDonHang)
            ->where('id_nguoi_dung', Auth::id())
            ->firstOrFail();

        return view('customer.checkout-success', compact('donHang'));
    }
}
