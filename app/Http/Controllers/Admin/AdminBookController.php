<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sach;
use App\Models\TheLoai;
use App\Models\NhaXuatBan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminBookController extends Controller
{
    // Hiển thị danh sách sách (index.blade.php)
    public function index()
    {
        $books = Sach::with('theLoai')->orderBy('id', 'desc')->paginate(10);

        return view('admin.books.index', compact('books'));
    }

    // Hiển thị form thêm mới sách (create.blade.php)
    public function create()
    {
        $categories = TheLoai::all();
        $publishers = NhaXuatBan::all();

        return view('admin.books.create', compact('categories', 'publishers'));
    }

    // Xử lý lưu sách mới vào CSDL nha_sach_db
    public function store(Request $request)
    {
        $request->validate([
            'tieu_de' => 'required|max:255',
            'id_the_loai' => 'required|exists:the_loai,id',
            'gia_ban' => 'required|numeric',
            'anh_bia' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $data = $request->except(['_token']);

        // Gán trạng thái hiển thị
        $data['dang_hoat_dong'] = $request->has('dang_hoat_dong') ? 1 : 0;

        // Tự động tạo slug từ tiêu đề sách nếu thiếu
        if (empty($data['duong_dan_tinh'])) {
            $data['duong_dan_tinh'] = Str::slug($request->tieu_de) . '-' . time();
        }

        // Xử lý lưu ảnh bìa sách
        if ($request->hasFile('anh_bia')) {
            $path = $request->file('anh_bia')->store('covers', 'public');
            $data['anh_bia'] = $path;
        }

        Sach::create($data);

        return redirect()->route('admin.books.index')->with('success', 'Thêm sách mới thành công!');
    }

    // Hiển thị form chỉnh sửa sách
    public function edit($id)
    {
        $book = Sach::findOrFail($id);
        $categories = TheLoai::all();
        $publishers = NhaXuatBan::all();

        return view('admin.books.edit', compact('book', 'categories', 'publishers'));
    }

    // Cập nhật thông tin sách
    public function update(Request $request, $id)
    {
        $book = Sach::findOrFail($id);

        $request->validate([
            'tieu_de' => 'required|max:255',
            'id_the_loai' => 'required|exists:the_loai,id',
            'gia_ban' => 'required|numeric',
            'anh_bia' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        // Loại bỏ _token và _method khỏi mảng dữ liệu update
        $data = $request->except(['_token', '_method']);

        // Xử lý nút bật/tắt hiển thị checkbox
        $data['dang_hoat_dong'] = $request->has('dang_hoat_dong') ? 1 : 0;

        // Xử lý cập nhật slug từ tieu_de
        if (empty($data['duong_dan_tinh'])) {
            $data['duong_dan_tinh'] = Str::slug($request->tieu_de) . '-' . $book->id;
        }

        // Cập nhật ảnh bìa mới và xóa ảnh bìa cũ trong đĩa
        if ($request->hasFile('anh_bia')) {
            if ($book->anh_bia && Storage::disk('public')->exists($book->anh_bia)) {
                Storage::disk('public')->delete($book->anh_bia);
            }

            $path = $request->file('anh_bia')->store('covers', 'public');
            $data['anh_bia'] = $path;
        }

        // Thực hiện lưu trực tiếp vào CSDL
        $book->update($data);

        return redirect()->route('admin.books.index')->with('success', 'Cập nhật thông tin sách thành công!');
    }

    // Xóa sách
    public function destroy($id)
    {
        $book = Sach::findOrFail($id);

        // Xóa ảnh bìa trong storage khi xóa sách
        if ($book->anh_bia && Storage::disk('public')->exists($book->anh_bia)) {
            Storage::disk('public')->delete($book->anh_bia);
        }

        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Xóa sách thành công!');
    }
}
