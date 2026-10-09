<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\KhoHang;
use App\Models\Sach;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAlertController extends Controller
{
    public function index(Request $request): View
    {
        $stockAlerts = $request->user()->theoDoiHang()
            ->with(['sach.khoHang'])
            ->latest('ngay_tao')
            ->paginate(12);

        return view('customer.stock-alerts.index', compact('stockAlerts'));
    }

    public function store(Request $request, Sach $book): RedirectResponse
    {
        abort_unless($book->dang_hoat_dong, 404);

        $isAvailable = DB::transaction(function () use ($request, $book): bool {
            $stock = KhoHang::where('id_sach', $book->id)->lockForUpdate()->first();

            if ($stock?->so_luong_kha_dung > 0) {
                return true;
            }

            $request->user()->theoDoiHang()->updateOrCreate(
                ['id_sach' => $book->id],
                ['da_thong_bao_at' => null],
            );

            return false;
        });

        if ($isAvailable) {
            return back()->with('info', 'Sách hiện đang có hàng. Bạn có thể đặt mua ngay.');
        }

        return back()->with('success', 'Đã đăng ký theo dõi. Chúng tôi sẽ báo khi sách có hàng trở lại.');
    }

    public function destroy(Request $request, int $alertId): RedirectResponse
    {
        $request->user()->theoDoiHang()->whereKey($alertId)->firstOrFail()->delete();

        return back()->with('success', 'Đã ngừng theo dõi sách này.');
    }
}
