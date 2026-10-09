<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Sach;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index(): View
    {
        $books = Auth::user()->sachYeuThich()
            ->where('dang_hoat_dong', true)
            ->with(['tacGia', 'khoHang'])
            ->orderByPivot('ngay_tao', 'desc')
            ->paginate(12);

        return view('customer.wishlist.index', compact('books'));
    }

    public function store(Sach $book): RedirectResponse
    {
        abort_unless($book->dang_hoat_dong, 404);

        Auth::user()->sachYeuThich()->syncWithoutDetaching([$book->id]);

        return back()->with('success', 'Đã thêm sách vào danh sách yêu thích.');
    }

    public function destroy(Sach $book): RedirectResponse
    {
        Auth::user()->sachYeuThich()->detach($book->id);

        return back()->with('success', 'Đã xóa sách khỏi danh sách yêu thích.');
    }
}
