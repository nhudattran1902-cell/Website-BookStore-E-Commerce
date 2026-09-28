<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TacGia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = TacGia::latest()->paginate(15);
        return view('admin.authors.index', compact('authors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ten_tac_gia' => 'required|string|max:150|unique:tac_gia,ten_tac_gia',
            'quoc_tich'   => 'nullable|string|max:100',
            'tieu_su'     => 'nullable|string',
        ]);

        TacGia::create([
            'ten_tac_gia' => $request->ten_tac_gia,
            'quoc_tich'   => $request->quoc_tich,
            'tieu_su'     => $request->tieu_su,
        ]);

        return redirect()->route('admin.authors.index')->with('success', 'Thêm tác giả thành công!');
    }

    public function update(Request $request, $id)
    {
        $tacGia = TacGia::findOrFail($id);

        $request->validate([
            'ten_tac_gia' => 'required|string|max:150|unique:tac_gia,ten_tac_gia,' . $id,
            'quoc_tich'   => 'nullable|string|max:100',
            'tieu_su'     => 'nullable|string',
        ]);

        $tacGia->update([
            'ten_tac_gia' => $request->ten_tac_gia,
            'quoc_tich'   => $request->quoc_tich,
            'tieu_su'     => $request->tieu_su,
        ]);

        return redirect()->route('admin.authors.index')->with('success', 'Cập nhật tác giả thành công!');
    }

    public function destroy($id)
    {
        TacGia::findOrFail($id)->delete();
        return redirect()->route('admin.authors.index')->with('success', 'Đã xóa tác giả!');
    }
}

