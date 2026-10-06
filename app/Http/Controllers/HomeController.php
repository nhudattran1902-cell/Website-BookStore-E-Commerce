<?php

namespace App\Http\Controllers;

use App\Models\Sach;
use App\Models\TacGia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function weeklyDealsQuery()
    {
        $query = Sach::with(['tacGia', 'khoHang'])
            ->where('dang_hoat_dong', true);

        if (Schema::hasColumn('sach', 'gia_khuyen_mai')) {
            $query->whereNotNull('gia_khuyen_mai')
                ->whereColumn('gia_khuyen_mai', '<', 'gia_ban');
        }

        return $query->latest('ngay_cap_nhat')->limit(4);
    }

    public function index()
    {
        // 1. Thể loại (cho phần categories)
        $categories = DB::table('the_loai')->limit(6)->get();

        $salesByBook = fn () => DB::table('chi_tiet_don_hang')
            ->join('don_hang', 'don_hang.id', '=', 'chi_tiet_don_hang.id_don_hang')
            ->where('don_hang.trang_thai', '!=', 'da_huy')
            ->select('chi_tiet_don_hang.id_sach')
            ->selectRaw('SUM(chi_tiet_don_hang.so_luong) as purchased_quantity')
            ->groupBy('chi_tiet_don_hang.id_sach');

        $popularBooks = fn () => Sach::query()
            ->with(['tacGia', 'khoHang'])
            ->leftJoinSub($salesByBook(), 'book_sales', 'sach.id', '=', 'book_sales.id_sach')
            ->select('sach.*')
            ->selectRaw('COALESCE(book_sales.purchased_quantity, 0) as purchased_quantity')
            ->where('sach.dang_hoat_dong', true);

        // Bán chạy dựa trên số cuốn đã đặt, không tính đơn đã hủy.
        $bestsellingBooks = $popularBooks()
            ->orderByDesc('purchased_quantity')
            ->orderByDesc('sach.id')
            ->limit(5)
            ->get();

        // Hiển thị nhóm tiếp theo theo lượng mua để hai dải sách không trùng nhau.
        $featuredBooks = $popularBooks()
            ->orderByDesc('purchased_quantity')
            ->orderByDesc('sach.id')
            ->offset(5)
            ->limit(5)
            ->get();

        if ($featuredBooks->isEmpty()) {
            $featuredBooks = $bestsellingBooks;
        }

        $weeklyDeals = $this->weeklyDealsQuery()->get();

        // 4. Tác giả (cho phần authors)
        $authors = TacGia::withCount('sach')
            ->orderByDesc('sach_count')
            ->take(5)
            ->get();

        return view('home.index', compact('categories', 'featuredBooks', 'bestsellingBooks', 'weeklyDeals', 'authors'));
    }
}
