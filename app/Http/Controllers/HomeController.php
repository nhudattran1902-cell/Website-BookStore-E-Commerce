<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            ->select('sach.*', 'tac_gia.ten_tac_gia as author_name')
            ->where('sach.dang_hoat_dong', 1)
            ->limit(5)
            ->get();

        // 3. Sách nổi bật ngẫu nhiên (cho phần featured-books)
        $featuredBooks = DB::table('sach')
            ->leftJoin('sach_tac_gia', 'sach.id', '=', 'sach_tac_gia.id_sach')
            ->leftJoin('tac_gia', 'sach_tac_gia.id_tac_gia', '=', 'tac_gia.id')
            ->select('sach.*', 'tac_gia.ten_tac_gia as author_name')
            ->where('sach.dang_hoat_dong', 1)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        // 4. Tác giả (cho phần authors)
        $authors = DB::table('tac_gia')->limit(5)->get();

        return view('home.index', compact('categories', 'bestsellingBooks', 'featuredBooks', 'authors'));
    }
}
       