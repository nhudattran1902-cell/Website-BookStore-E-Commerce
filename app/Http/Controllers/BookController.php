<?php

namespace App\Http\Controllers;

use App\Models\Sach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(Request $request)
    {
        // 1. Lấy danh sách thể loại từ bảng `the_loai` để hiển thị menu lọc
        $categories = DB::table('the_loai')->get();

        // 2. Truy vấn danh sách sách từ bảng `sach`, kết hợp bảng trung gian `sach_tac_gia` và `tac_gia`
        $query = DB::table('sach')
            ->leftJoin('sach_tac_gia', 'sach.id', '=', 'sach_tac_gia.id_sach')
            ->leftJoin('tac_gia', 'sach_tac_gia.id_tac_gia', '=', 'tac_gia.id')
            ->select(
                'sach.*',
                'tac_gia.ten_tac_gia as author_name'
            )
            ->where('sach.dang_hoat_dong', 1);

        // 3. Xử lý lọc theo thể loại nếu người dùng click chọn trên giao diện
        if ($request->has('the_loai')) {
            $query->where('sach.id_the_loai', $request->the_loai);
        }

        // 4. Sắp xếp theo giá, mặc định giữ nguyên thứ tự theo ID mới nhất
        $sort = $request->input('sort');
        if ($sort === 'price_asc') {
            $query->orderBy('sach.gia_ban', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('sach.gia_ban', 'desc');
        } else {
            $query->orderBy('sach.id', 'desc');
        }

        // 5. Phân trang (8 cuốn sách mỗi trang) và giữ lại query string khi chuyển trang
        $books = $query->paginate(8)->withQueryString();

        // 6. Trả về view danh sách sách trong thư mục books/index.blade.php
        return view('books.index', compact('books', 'categories'));
    }
    public function show($id)
    {
        // 1. Lấy thông tin sách kèm theo Nhà xuất bản và Thể loại
        $book = DB::table('sach')
            ->leftJoin('the_loai', 'sach.id_the_loai', '=', 'the_loai.id')
            ->leftJoin('nha_xuat_ban', 'sach.id_nha_xuat_ban', '=', 'nha_xuat_ban.id')
            ->select(
                'sach.*',
                'the_loai.ten_the_loai',
                'nha_xuat_ban.ten_nxb'
            )
            ->where('sach.id', $id)
            ->first();

        if (!$book) {
            abort(404, 'Không tìm thấy cuốn sách này.');
        }

        // 2. Lấy danh sách tác giả của cuốn sách (qua bảng trung gian sach_tac_gia)
        $authors = DB::table('sach_tac_gia')
            ->join('tac_gia', 'sach_tac_gia.id_tac_gia', '=', 'tac_gia.id')
            ->where('sach_tac_gia.id_sach', $id)
            ->select('tac_gia.*')
            ->get();

        // 3. Lấy các trang sách được phép đọc thử (từ bảng trang_sach)
        $previewPages = DB::table('trang_sach')
            ->where('id_sach', $id)
            ->where('cho_phep_doc_thu', 1)
            ->orderBy('so_trang', 'asc')
            ->get();

        // Trả về view chi tiết sách
        return view('books.show', compact('book', 'authors', 'previewPages'));
    }

    public function search(Request $request)
    {
        $keyword = $request->input('q');

        // Tìm kiếm theo tiêu đề sách hoặc tên thể loại
        $books = Sach::with('theLoai')
            ->where('dang_hoat_dong', 1)
            ->where(function ($query) use ($keyword) {
                $query->where('tieu_de', 'LIKE', "%{$keyword}%")
                    ->orWhereHas('theLoai', function ($q) use ($keyword) {
                        $q->where('ten_the_loai', 'LIKE', "%{$keyword}%");
                    });
            })
            ->paginate(12);

        return view('books.search', compact('books', 'keyword'));
    }
}
