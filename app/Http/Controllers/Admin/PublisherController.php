<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NhaXuatBan;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
    public function index()
    {
        $publishers = NhaXuatBan::latest()->paginate(15);
        return view('admin.publishers.index', compact('publishers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ten_nxb'  => 'required|string|max:200|unique:nha_xuat_ban,ten_nxb',
            'dia_chi'  => 'nullable|string|max:500',
            'website'  => 'nullable|url|max:255',
        ]);

        NhaXuatBan::create($request->only(['ten_nxb', 'dia_chi', 'website']));

        return redirect()->route('admin.publishers.index')->with('success', 'Thêm nhà xuất bản thành công!');
    }

    public function update(Request $request, $id)
    {
        $nxb = NhaXuatBan::findOrFail($id);

        $request->validate([
            'ten_nxb' => 'required|string|max:200|unique:nha_xuat_ban,ten_nxb,' . $id,
            'dia_chi' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
        ]);

        $nxb->update($request->only(['ten_nxb', 'dia_chi', 'website']));

        return redirect()->route('admin.publishers.index')->with('success', 'Cập nhật nhà xuất bản thành công!');
    }

    public function destroy($id)
    {
        NhaXuatBan::findOrFail($id)->delete();
        return redirect()->route('admin.publishers.index')->with('success', 'Đã xóa nhà xuất bản!');
    }
}

