<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DanhGiaSach;
use App\Models\DonHang;
use App\Models\Sach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request, int $bookId): RedirectResponse
    {
        Sach::where('dang_hoat_dong', true)->findOrFail($bookId);

        $validated = $request->validate([
            'so_sao' => 'required|integer|min:1|max:5',
            'binh_luan' => 'required|string|max:2000',
        ], [
            'so_sao.required' => 'Vui lòng chọn số sao đánh giá.',
            'so_sao.min' => 'Số sao tối thiểu là 1.',
            'so_sao.max' => 'Số sao tối đa là 5.',
            'binh_luan.required' => 'Vui lòng nhập nội dung bình luận.',
            'binh_luan.max' => 'Bình luận không vượt quá 2000 ký tự.',
        ]);

        $hasCompletedPurchase = DonHang::query()
            ->where('id_nguoi_dung', $request->user()->id)
            ->where('trang_thai', 'hoan_thanh')
            ->whereHas('chiTietDonHang', function ($query) use ($bookId): void {
                $query->where('id_sach', $bookId);
            })
            ->exists();

        if (! $hasCompletedPurchase) {
            throw ValidationException::withMessages([
                'review' => 'Bạn chỉ có thể đánh giá sách trong đơn hàng đã hoàn tất.',
            ]);
        }

        DanhGiaSach::updateOrCreate(
            [
                'id_nguoi_dung' => $request->user()->id,
                'id_sach' => $bookId,
            ],
            [
                'so_sao' => $validated['so_sao'],
                'binh_luan' => $validated['binh_luan'],
                'da_duyet' => false,
            ],
        );

        return redirect()->back()->with('success', 'Cảm ơn bạn đã gửi đánh giá!');
    }
}
