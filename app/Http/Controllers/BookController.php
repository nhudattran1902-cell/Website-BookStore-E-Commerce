<?php

namespace App\Http\Controllers;

use App\Models\Sach;
use App\Models\TheLoai;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Hiển thị danh sách sách cửa hàng (lọc thể loại, sắp xếp giá, phân trang)
     */
    public function index(Request $request): View
    {
        // 1. Lấy danh sách thể loại cho sidebar/menu lọc
        $categories = TheLoai::all();

        // 2. Truy vấn danh sách sách đang hoạt động kèm thông tin tác giả và kho hàng
        $query = Sach::with(['tacGia', 'khoHang'])
            ->where('dang_hoat_dong', true);

        // 3. Lọc theo thể loại nếu người dùng chọn
        if ($request->filled('the_loai')) {
            $query->where('id_the_loai', $request->the_loai);
        }

        // 4. Sắp xếp theo giá hoặc ID mới nhất
        $sort = $request->input('sort');
        if ($sort === 'price_asc') {
            $query->orderBy('gia_ban', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('gia_ban', 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        // 5. Phân trang 8 cuốn/trang và giữ lại query string khi đổi trang
        $books = $query->paginate(8)->withQueryString();

        return view('books.index', compact('books', 'categories'));
    }

    /**
     * Hiển thị chi tiết cuốn sách (kèm tác giả, trang đọc thử, đánh giá đã duyệt, điểm sao, sách liên quan & sách đã xem)
     */
    public function show(int $id): View
    {
        // 1. Lấy thông tin sách cùng các quan hệ liên quan
        $book = Sach::with(['theLoai', 'nhaXuatBan', 'tacGia', 'khoHang', 'trangSach'])
            ->where('dang_hoat_dong', true)
            ->findOrFail($id);

        // 2. XỬ LÝ SESSION SÁCH ĐÃ XEM (RECENTLY VIEWED)
        $recentlyViewedIds = session()->get('recently_viewed_books', []);

        // Loại bỏ ID sách hiện tại nếu đã tồn tại trước đó để đưa lên đầu danh sách
        $recentlyViewedIds = array_diff($recentlyViewedIds, [$book->id]);

        // Thêm ID sách hiện tại vào đầu danh sách
        array_unshift($recentlyViewedIds, $book->id);

        // Giới hạn lưu tối đa 10 ID sách gần đây trong session
        $recentlyViewedIds = array_slice($recentlyViewedIds, 0, 10);

        // Cập nhật lại danh sách ID vào Session
        session()->put('recently_viewed_books', $recentlyViewedIds);

        // 3. TRUY VẤN DANH SÁCH SÁCH ĐÃ XEM ĐỂ HIỂN THỊ (Loại trừ sách đang xem hiện tại)
        $idsToFetch = array_diff($recentlyViewedIds, [$book->id]);

        $recentlyViewedBooks = collect();
        if (! empty($idsToFetch)) {
            $recentlyViewedBooks = Sach::with(['tacGia', 'theLoai', 'khoHang'])
                ->where('dang_hoat_dong', true)
                ->whereIn('id', $idsToFetch)
                ->get()
                // Giữ nguyên thứ tự xem gần đây nhất từ mảng Session
                ->sortBy(fn($item) => array_search($item->id, $idsToFetch));
        }

        // 4. Lấy danh sách tác giả (alias cho view cũ)
        $authors = $book->tacGia;

        // 5. Lấy các trang sách đọc thử
        $previewPages = $book->trangSach()
            ->where('cho_phep_doc_thu', true)
            ->orderBy('so_trang', 'asc')
            ->get();

        // 6. Lấy danh sách đánh giá đã duyệt (da_duyet = true) kèm người dùng
        $reviews = $book->danhGia()
            ->with('nguoiDung')
            ->where('da_duyet', true)
            ->latest('ngay_tao')
            ->paginate(5);

        // 7. Tính điểm sao trung bình và tổng lượt đánh giá
        $avgRating = round((float) $book->danhGia()->where('da_duyet', true)->avg('so_sao'), 1);
        $totalReviews = $book->danhGia()->where('da_duyet', true)->count();

        // 8. Lấy danh sách sách liên quan (cùng thể loại, loại trừ sách hiện tại, ngẫu nhiên 4 cuốn)
        $relatedBooks = Sach::with(['tacGia', 'theLoai', 'khoHang'])
            ->where('dang_hoat_dong', true)
            ->where('id_the_loai', $book->id_the_loai)
            ->where('id', '!=', $book->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('books.show', compact(
            'book',
            'authors',
            'previewPages',
            'reviews',
            'avgRating',
            'totalReviews',
            'relatedBooks',
            'recentlyViewedBooks'
        ));
    }

    /**
     * Tìm kiếm sách theo tiêu đề hoặc tên thể loại
     */
    public function search(Request $request): View
    {
        $keyword = $request->input('q') ?? $request->input('keyword');

        $books = Sach::with(['theLoai', 'tacGia'])
            ->where('dang_hoat_dong', true)
            ->where(function ($query) use ($keyword) {
                $query->where('tieu_de', 'LIKE', "%{$keyword}%")
                    ->orWhereHas('theLoai', function ($q) use ($keyword) {
                        $q->where('ten_the_loai', 'LIKE', "%{$keyword}%");
                    });
            })
            ->paginate(12)
            ->withQueryString();

        return view('books.search', compact('books', 'keyword'));
    }
}
