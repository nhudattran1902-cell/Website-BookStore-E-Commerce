<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThanhToan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Danh sách giao dịch thanh toán kèm bộ lọc.
     */
    public function index(Request $request): View
    {
        $query = ThanhToan::with(['donHang.nguoiDung']);

        // 1. Lọc theo từ khóa (Mã đơn hàng, mã giao dịch)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ma_giao_dich', 'like', "%{$search}%")
                    ->orWhereHas('donHang', function ($qDon) use ($search) {
                        $qDon->where('ma_don_hang', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Lọc theo phương thức thanh toán (hỗ trợ cả 'method' và 'phuong_thuc_thanh_toan')
        $paymentMethod = $request->input('method') ?? $request->input('phuong_thuc_thanh_toan');
        if (!empty($paymentMethod)) {
            $query->where('phuong_thuc_thanh_toan', $paymentMethod);
        }

        // 3. Lọc theo trạng thái thanh toán
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->input('status'));
        }

        // 4. Lọc theo khoảng thời gian
        if ($request->filled('date_from')) {
            $query->whereDate('ngay_tao', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('ngay_tao', '<=', $request->input('date_to'));
        }

        $transactions = $query->latest('ngay_tao')
            ->paginate(15)
            ->withQueryString();

        return view('admin.transactions.index', compact('transactions'));
    }

    /**
     * Chi tiết giao dịch thanh toán.
     */
    public function show(string $id): View
    {
        $transaction = ThanhToan::with(['donHang.nguoiDung', 'donHang.chiTietDonHang.sach'])->findOrFail($id);

        return view('admin.transactions.show', compact('transaction'));
    }
}
