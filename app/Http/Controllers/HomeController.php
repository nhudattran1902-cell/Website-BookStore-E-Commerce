<?php

namespace App\Http\Controllers;

use App\Models\TacGia;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Thể loại (cho phần categories)
        $categories = DB::table('the_loai')->limit(6)->get();

        // 2. Sách bán chạy (cho phần bestselling-books)
        $bestsellingBooks = DB::table('sach')
            ->leftJoin('sach_tac_gia', 'sach.id', '=', 'sach_tac_gia.id_sach')
            ->leftJoin('tac_gia', 'sach_tac_gia.id_tac_gia', '=', 'tac_gia.id')
            ->leftJoin('kho_hang', 'sach.id', '=', 'kho_hang.id_sach')
            ->select('sach.*', 'tac_gia.ten_tac_gia as author_name', DB::raw('COALESCE(kho_hang.so_luong_ton, 0) as so_luong_ton'))
            ->where('sach.dang_hoat_dong', 1)
            ->limit(5)
            ->get();

        // 3. Sách nổi bật ngẫu nhiên (cho phần featured-books)
        $featuredBooks = DB::table('sach')
            ->leftJoin('sach_tac_gia', 'sach.id', '=', 'sach_tac_gia.id_sach')
            ->leftJoin('tac_gia', 'sach_tac_gia.id_tac_gia', '=', 'tac_gia.id')
            ->leftJoin('kho_hang', 'sach.id', '=', 'kho_hang.id_sach')
            ->select('sach.*', 'tac_gia.ten_tac_gia as author_name', DB::raw('COALESCE(kho_hang.so_luong_ton, 0) as so_luong_ton'))
            ->where('sach.dang_hoat_dong', 1)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        // 4. Tác giả (cho phần authors)
        $authors = TacGia::withCount('sach')
            ->orderByDesc('sach_count')
            ->take(5)
            ->get();

        return view('home.index', compact('categories', 'featuredBooks', 'authors','bestsellingBooks'));
    }
}
