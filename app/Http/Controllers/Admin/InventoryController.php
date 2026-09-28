<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoHang;
use App\Models\Sach;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Danh sách kho hàng, cảnh báo hàng sắp hết.
     */
    public function index()
    {
        $inventory = KhoHang::with('sach')
            ->orderBy('so_luong_ton', 'asc')
            ->paginate(20);

        // Danh sách sách chưa có bản ghi kho
        $sachChuaCoKho = Sach::whereDoesntHave('khoHang')->get();

        return view('admin.inventory.index', compact('inventory', 'sachChuaCoKho'));
    }

    /**
     * Điều chỉnh số lượng tồn kho.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'so_luong_ton'    => 'required|integer|min:0',
            'nguong_canh_bao' => 'nullable|integer|min:0',
        ]);

        $kho = KhoHang::findOrFail($id);
        $kho->update([
            'so_luong_ton'    => $request->so_luong_ton,
            'nguong_canh_bao' => $request->nguong_canh_bao ?? $kho->nguong_canh_bao,
        ]);

        return redirect()->route('admin.inventory.index')
            ->with('success', "Đã cập nhật kho cho sách: {$kho->sach->tieu_de}");
    }
}

