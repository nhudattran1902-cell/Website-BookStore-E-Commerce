<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoHang;
use App\Models\NhaXuatBan;
use App\Models\Sach;
use App\Models\TheLoai;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminBookController extends Controller
{
    // Hiển thị danh sách sách (index.blade.php)
    public function index(Request $request): View
    {
        $query = Sach::with(['khoHang', 'theLoai', 'nhaXuatBan'])
            ->latest('ngay_tao');

        if ($request->filled('search')) {
            $query->where('tieu_de', 'like', '%' . $request->search . '%');
        }

        // Đổi tên biến thành $books để khớp với compact('books') và View Blade
        $books = $query->paginate(10);

        return view('admin.books.index', compact('books'));
    }

    // Hiển thị form thêm mới sách (create.blade.php)
    public function create(): View
    {
        $categories = TheLoai::all();
        $publishers = NhaXuatBan::all();

        return view('admin.books.create', compact('categories', 'publishers'));
    }

    // Xử lý lưu sách mới vào CSDL nha_sach_db
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tieu_de' => 'required|max:255',
            'id_the_loai' => 'required|exists:the_loai,id',
            'gia_ban' => 'required|numeric',
            'so_luong_ton' => 'nullable|integer|min:0',
            'anh_bia' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except(['_token', 'so_luong_ton']);

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

        $book = Sach::create($data);

        // Tạo bản ghi tồn kho tương ứng
        KhoHang::create([
            'id_sach' => $book->id,
            'so_luong_ton' => $request->input('so_luong_ton', 0),
            'so_luong_dat_truoc' => 0,
        ]);

        return redirect()->route('admin.books.index')->with('success', 'Thêm sách mới thành công!');
    }

    // Hiển thị form chỉnh sửa sách
    public function edit(int $id): View
    {
        $book = Sach::with('khoHang')->findOrFail($id);
        $categories = TheLoai::all();
        $publishers = NhaXuatBan::all();

        return view('admin.books.edit', compact('book', 'categories', 'publishers'));
    }

    // Cập nhật thông tin sách
    public function update(Request $request, int $id): RedirectResponse
    {
        $book = Sach::findOrFail($id);

        $request->validate([
            'tieu_de' => 'required|max:255',
            'id_the_loai' => 'required|exists:the_loai,id',
            'gia_ban' => 'required|numeric',
            'so_luong_ton' => 'nullable|integer|min:0',
            'anh_bia' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except(['_token', '_method', 'so_luong_ton']);

        // Xử lý nút bật/tắt hiển thị checkbox
        $data['dang_hoat_dong'] = $request->has('dang_hoat_dong') ? 1 : 0;

        // Xử lý cập nhật slug từ tieu_de
        if (empty($data['duong_dan_tinh'])) {
            $data['duong_dan_tinh'] = Str::slug($request->tieu_de) . '-' . $book->id;
        }

        // Cập nhật ảnh bìa mới và xóa ảnh bìa cũ
        if ($request->hasFile('anh_bia')) {
            if ($book->anh_bia && Storage::disk('public')->exists($book->anh_bia)) {
                Storage::disk('public')->delete($book->anh_bia);
            }

            $path = $request->file('anh_bia')->store('covers', 'public');
            $data['anh_bia'] = $path;
        }

        $book->update($data);

        // Cập nhật số lượng tồn kho nếu có truyền vào
        if ($request->has('so_luong_ton')) {
            KhoHang::updateOrCreate(
                ['id_sach' => $book->id],
                ['so_luong_ton' => $request->input('so_luong_ton', 0)]
            );
        }

        return redirect()->route('admin.books.index')->with('success', 'Cập nhật thông tin sách thành công!');
    }

    // Xóa sách
    public function destroy(int $id): RedirectResponse
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