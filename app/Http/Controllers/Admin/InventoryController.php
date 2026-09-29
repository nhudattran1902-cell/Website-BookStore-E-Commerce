<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoHang;
use App\Models\Sach;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Danh sách kho hàng, tự động sinh bản ghi kho nếu chưa có.
     */
    public function index(Request $request)
    {
        // 1. Tự động kiểm tra và khởi tạo bản ghi kho cho tất cả sách chưa có
        $sachChuaCoKho = Sach::whereDoesntHave('khoHang')->get();
        foreach ($sachChuaCoKho as $sach) {
            KhoHang::firstOrCreate(
                ['id_sach' => $sach->id],
                [
                    'so_luong_ton' => 0,
                    'nguong_canh_bao' => 5,
                ]
            );
        }

        // 2. Truy vấn danh sách kho hàng
        $query = KhoHang::with('sach');

        // Lọc theo sách nếu có tham số id_sach từ URL
        if ($request->filled('id_sach')) {
            $query->where('id_sach', $request->input('id_sach'));
        }

        $inventory = $query->orderBy('so_luong_ton', 'asc')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.inventory.index', compact('inventory'));
    }

    /**
     * Cập nhật số lượng tồn kho hoặc nhập hàng nhanh.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'so_luong_ton' => 'nullable|integer|min:0',
            'so_luong_nhap' => 'nullable|integer|min:1',
            'nguong_canh_bao' => 'nullable|integer|min:0',
        ]);

        $kho = KhoHang::findOrFail($id);

        // Trường hợp 1: Nhập hàng nhanh (Cộng dồn số lượng)
        if ($request->filled('so_luong_nhap')) {
            $kho->so_luong_ton += (int) $request->so_luong_nhap;
            if ($request->filled('nguong_canh_bao')) {
                $kho->nguong_canh_bao = $request->nguong_canh_bao;
            }
            $kho->save();

            return redirect()->back()
                ->with('success', "Đã nhập thành công {$request->so_luong_nhap} cuốn cho sách: {$kho->sach->tieu_de}");
        }

        // Trường hợp 2: Cập nhật trực tiếp số lượng tồn
        $kho->update([
            'so_luong_ton' => $request->so_luong_ton ?? $kho->so_luong_ton,
            'nguong_canh_bao' => $request->nguong_canh_bao ?? $kho->nguong_canh_bao,
        ]);

        return redirect()->back()
            ->with('success', "Đã cập nhật kho cho sách: {$kho->sach->tieu_de}");
    }
}
