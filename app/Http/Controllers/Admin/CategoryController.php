<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TheLoai;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $danhSachTheLoai = TheLoai::latest()->paginate(10);
        return view('admin.categories.index', compact('danhSachTheLoai'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ten_the_loai' => 'required|max:255|unique:the_loai,ten_the_loai',
            'mo_ta' => 'nullable|string'
        ]);

        TheLoai::create([
            'ten_the_loai' => $request->ten_the_loai,
            'duong_dan_tinh' => Str::slug($request->ten_the_loai),
            'mo_ta' => $request->mo_ta
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Thêm thể loại thành công!');
    }

    public function update(Request $request, $id)
    {
        $theLoai = TheLoai::findOrFail($id);

        $request->validate([
            'ten_the_loai' => 'required|max:255|unique:the_loai,ten_the_loai,' . $id,
            'mo_ta' => 'nullable|string'
        ]);

        $theLoai->update([
            'ten_the_loai' => $request->ten_the_loai,
            'duong_dan_tinh' => Str::slug($request->ten_the_loai),
            'mo_ta' => $request->mo_ta
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Cập nhật thể loại thành công!');
    }

    public function destroy($id)
    {
        $theLoai = TheLoai::findOrFail($id);
        $theLoai->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Xóa thể loại thành công!');
    }
}
