<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhGiaSach;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = DanhGiaSach::with(['nguoiDung', 'sach'])->latest('ngay_tao');

        if ($request->filled('status')) {
            $query->where('da_duyet', $request->status === 'approved');
        }

        $reviews = $query->paginate(10)->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function toggleApprove(int $id): RedirectResponse
    {
        $review = DanhGiaSach::findOrFail($id);
        $review->update(['da_duyet' => !$review->da_duyet]);

        return redirect()->back()->with('success', 'Cập nhật trạng thái đánh giá thành công!');
    }

    public function destroy(int $id): RedirectResponse
    {
        $review = DanhGiaSach::findOrFail($id);
        $review->delete();

        return redirect()->back()->with('success', 'Xóa đánh giá thành công!');
    }
}
