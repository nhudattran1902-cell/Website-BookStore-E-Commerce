<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TacGia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $query = TacGia::withCount('sach');

        if ($request->filled('search')) {
            $query->where('ten_tac_gia', 'like', '%'.$request->search.'%');
        }

        $authors = $query->latest('ngay_tao')->paginate(10);

        return view('admin.authors.index', compact('authors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ten_tac_gia' => 'required|string|max:255',
            'tieu_su' => 'nullable|string',
            'anh_dai_dien' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'ten_tac_gia.required' => 'Vui lòng nhập tên tác giả.',
            'anh_dai_dien.image' => 'Tập tin tải lên phải là hình ảnh.',
            'anh_dai_dien.max' => 'Kích thước ảnh tối đa là 2MB.',
        ]);

        $data = [
            'ten_tac_gia' => $request->ten_tac_gia,
            'slug' => Str::slug($request->ten_tac_gia),
            'tieu_su' => $request->tieu_su,
        ];

        if ($request->hasFile('anh_dai_dien')) {
            $data['anh_dai_dien'] = $request->file('anh_dai_dien')->store('authors', 'public');
        }

        TacGia::create($data);

        return redirect()->route('admin.authors.index')->with('success', 'Thêm tác giả mới thành công!');
    }

    public function update(Request $request, string $id)
    {
        $author = TacGia::findOrFail($id);

        $request->validate([
            'ten_tac_gia' => 'required|string|max:255',
            'tieu_su' => 'nullable|string',
            'anh_dai_dien' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'ten_tac_gia.required' => 'Vui lòng nhập tên tác giả.',
            'anh_dai_dien.image' => 'Tập tin tải lên phải là hình ảnh.',
            'anh_dai_dien.max' => 'Kích thước ảnh tối đa là 2MB.',
        ]);

        $data = [
            'ten_tac_gia' => $request->ten_tac_gia,
            'slug' => Str::slug($request->ten_tac_gia),
            'tieu_su' => $request->tieu_su,
        ];

        if ($request->hasFile('anh_dai_dien')) {
            // Xóa ảnh cũ nếu có
            if ($author->anh_dai_dien && Storage::disk('public')->exists($author->anh_dai_dien)) {
                Storage::disk('public')->delete($author->anh_dai_dien);
            }
            $data['anh_dai_dien'] = $request->file('anh_dai_dien')->store('authors', 'public');
        }

        $author->update($data);

        return redirect()->route('admin.authors.index')->with('success', 'Cập nhật thông tin tác giả thành công!');
    }

    public function destroy(string $id)
    {
        $author = TacGia::findOrFail($id);

        if ($author->anh_dai_dien && Storage::disk('public')->exists($author->anh_dai_dien)) {
            Storage::disk('public')->delete($author->anh_dai_dien);
        }

        $author->delete();

        return redirect()->route('admin.authors.index')->with('success', 'Xóa tác giả thành công!');
    }
}
