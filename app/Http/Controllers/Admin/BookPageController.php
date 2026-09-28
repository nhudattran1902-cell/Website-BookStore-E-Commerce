<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sach;
use App\Models\TrangSach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookPageController extends Controller
{
    /**
     * Hiển thị danh sách các trang đọc thử của sách.
     */
    public function index($bookId)
    {
        $book = Sach::findOrFail($bookId);
        $pages = TrangSach::where('id_sach', $bookId)
            ->orderBy('so_trang', 'asc')
            ->get();

        $nextPageNumber = ($pages->max('so_trang') ?? 0) + 1;

        return view('admin.books.pages.index', compact('book', 'pages', 'nextPageNumber'));
    }

    /**
     * Thêm trang đọc thử mới cho sách.
     * Hỗ trợ tải 1 trang hoặc nhiều trang cùng lúc.
     */
    public function store(Request $request, $bookId)
    {
        $book = Sach::findOrFail($bookId);

        $request->validate([
            'so_trang' => 'required|integer|min:1',
            'anh_trang' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'danh_sach_anh' => 'nullable|array',
            'danh_sach_anh.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'so_trang.required' => 'Vui lòng nhập số thứ tự trang.',
            'so_trang.integer' => 'Số trang phải là số nguyên dương.',
            'anh_trang.image' => 'File tải lên phải là hình ảnh hợp lệ.',
            'danh_sach_anh.*.image' => 'Tất cả các file tải lên phải là hình ảnh.',
        ]);

        $choPhepDocThu = $request->has('cho_phep_doc_thu') ? 1 : 0;
        $startPage = (int) $request->input('so_trang');

        // Trường hợp 1: Tải nhiều trang cùng lúc
        if ($request->hasFile('danh_sach_anh')) {
            $files = $request->file('danh_sach_anh');
            $currentPage = $startPage;

            foreach ($files as $file) {
                $path = $file->store('book_pages/' . $bookId, 'public');

                TrangSach::create([
                    'id_sach' => $bookId,
                    'so_trang' => $currentPage,
                    'duong_dan_anh' => $path,
                    'cho_phep_doc_thu' => $choPhepDocThu,
                ]);

                $currentPage++;
            }

            return redirect()->route('admin.books.pages.index', $bookId)
                ->with('success', 'Đã thêm thành công ' . count($files) . ' trang sách đọc thử!');
        }

        // Trường hợp 2: Tải 1 trang đơn lẻ
        if ($request->hasFile('anh_trang')) {
            $file = $request->file('anh_trang');
            $path = $file->store('book_pages/' . $bookId, 'public');

            TrangSach::create([
                'id_sach' => $bookId,
                'so_trang' => $startPage,
                'duong_dan_anh' => $path,
                'cho_phep_doc_thu' => $choPhepDocThu,
            ]);

            return redirect()->route('admin.books.pages.index', $bookId)
                ->with('success', 'Đã thêm trang số ' . $startPage . ' thành công!');
        }

        return redirect()->back()->with('error', 'Vui lòng chọn hình ảnh trang sách.');
    }

    /**
     * Cập nhật thông tin trang sách (chỉnh số trang, đổi ảnh, bật/tắt đọc thử).
     */
    public function update(Request $request, $bookId, $pageId)
    {
        $book = Sach::findOrFail($bookId);
        $page = TrangSach::where('id_sach', $bookId)->findOrFail($pageId);

        $request->validate([
            'so_trang' => 'required|integer|min:1',
            'anh_trang' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $data = [
            'so_trang' => $request->input('so_trang'),
            'cho_phep_doc_thu' => $request->has('cho_phep_doc_thu') ? 1 : 0,
        ];

        // Nếu admin tải ảnh mới thay thế
        if ($request->hasFile('anh_trang')) {
            if ($page->duong_dan_anh && Storage::disk('public')->exists($page->duong_dan_anh)) {
                Storage::disk('public')->delete($page->duong_dan_anh);
            }

            $path = $request->file('anh_trang')->store('book_pages/' . $bookId, 'public');
            $data['duong_dan_anh'] = $path;
        }

        $page->update($data);

        return redirect()->route('admin.books.pages.index', $bookId)
            ->with('success', 'Đã cập nhật trang số ' . $page->so_trang . ' thành công!');
    }

    /**
     * Xóa một trang sách đọc thử.
     */
    public function destroy($bookId, $pageId)
    {
        $page = TrangSach::where('id_sach', $bookId)->findOrFail($pageId);

        // Xóa file ảnh trên disk
        if ($page->duong_dan_anh && Storage::disk('public')->exists($page->duong_dan_anh)) {
            Storage::disk('public')->delete($page->duong_dan_anh);
        }

        $pageNumber = $page->so_trang;
        $page->delete();

        return redirect()->route('admin.books.pages.index', $bookId)
            ->with('success', 'Đã xóa trang số ' . $pageNumber . ' thành công!');
    }
}

