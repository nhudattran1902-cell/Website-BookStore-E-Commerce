<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DanhGiaSach;
use App\Models\Sach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, int $bookId): RedirectResponse
    {
        Sach::where('dang_hoat_dong', true)->findOrFail($bookId);

        $request->validate([
            'so_sao' => 'required|integer|min:1|max:5',
            'binh_luan' => 'required|string|max:1000',
        ]);

        DanhGiaSach::create([
            'id_nguoi_dung' => auth()->id(),
            'id_sach' => $bookId,
            'so_sao' => $request->so_sao,
            'binh_luan' => $request->binh_luan,
            'da_duyet' => true, // Mặc định hiển thị ngay
        ]);

        return redirect()->back()->with('success', 'Cảm ơn bạn đã gửi đánh giá!');
    }
}