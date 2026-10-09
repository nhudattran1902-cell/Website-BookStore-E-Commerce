<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Services\AdminActionLogger;
use App\Services\OrderWorkflowService;
use App\Services\VietQRService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LogicException;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng phía Admin (Bao gồm bộ lọc tìm kiếm & ngoại lệ giao hàng/RTO)
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'trang_thai' => ['nullable', 'in:cho_xu_ly,dang_xu_ly,dang_giao,hoan_thanh,da_huy'],
            'phuong_thuc_thanh_toan' => ['nullable', 'in:COD,MoMo,VNPay,BankTransfer'],
            'don_vi_van_chuyen' => ['nullable', 'string', 'max:50'],
            'ngoai_le' => ['nullable', 'in:giao_that_bai,rto_chuyen_hoan'],
            'sort_total' => ['nullable', 'in:asc,desc'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = DonHang::with(['nguoiDung', 'thanhToan', 'chiTietDonHang.sach']);

        // 1. Tìm kiếm theo Mã đơn hàng, Tên người nhận, SĐT hoặc Email khách hàng
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('ma_don_hang', 'like', "%{$search}%")
                    ->orWhere('ten_nguoi_nhan', 'like', "%{$search}%")
                    ->orWhere('sdt_nguoi_nhan', 'like', "%{$search}%")
                    ->orWhereHas('nguoiDung', function ($userQuery) use ($search): void {
                        $userQuery->where('ho_ten', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Lọc theo trạng thái đơn hàng
        if (! empty($filters['trang_thai'])) {
            $query->where('trang_thai', $filters['trang_thai']);
        }

        // 3. Lọc theo phương thức thanh toán
        if (! empty($filters['phuong_thuc_thanh_toan'])) {
            $query->whereHas('thanhToan', function ($paymentQuery) use ($filters): void {
                $paymentQuery->where('phuong_thuc_thanh_toan', $filters['phuong_thuc_thanh_toan']);
            });
        }

        // 4. Lọc theo Đơn vị vận chuyển (GHN / GHTK / Viettel Post...)
        if (! empty($filters['don_vi_van_chuyen'])) {
            $query->where('don_vi_van_chuyen', $filters['don_vi_van_chuyen']);
        }

        // 5. Lọc ngoại lệ CSKH: Đơn giao thất bại hoặc Đơn chuyển hoàn RTO
        if (! empty($filters['ngoai_le'])) {
            if ($filters['ngoai_le'] === 'giao_that_bai') {
                $query->where('trang_thai', 'dang_giao')
                    ->whereHas('lichSuDonHang', function ($logQuery): void {
                        $logQuery->where('ghi_chu', 'like', '%thất bại%')
                            ->orWhere('ghi_chu', 'like', '%giao lại%');
                    });
            } elseif ($filters['ngoai_le'] === 'rto_chuyen_hoan') {
                $query->where('trang_thai', 'da_huy')
                    ->whereHas('lichSuDonHang', function ($logQuery): void {
                        $logQuery->where('ghi_chu', 'like', '%chuyển hoàn%')
                            ->orWhere('ghi_chu', 'like', '%RTO%');
                    });
            }
        }

        // 6. Lọc theo khoảng ngày tạo đơn
        if (! empty($filters['date_from'])) {
            $query->whereDate('ngay_tao', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('ngay_tao', '<=', $filters['date_to']);
        }

        // 7. Sắp xếp theo tổng tiền hoặc ngày tạo mới nhất
        if (! empty($filters['sort_total'])) {
            $query->orderBy('thanh_tien', $filters['sort_total']);
        } else {
            $query->orderByDesc('ngay_tao');
        }

        $orders = $query->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Xem chi tiết một đơn hàng kèm lịch sử nhật ký sự kiện
     */
    public function show(int $id): View
    {
        $order = DonHang::with(['nguoiDung', 'maGiamGia', 'chiTietDonHang.sach', 'thanhToan', 'lichSuDonHang.nguoiThayDoi'])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái đơn hàng theo quy tắc khống chế & Xử lý tồn kho (Reserve / Release)
     */
    public function updateStatus(
        Request $request,
        int $id,
        OrderWorkflowService $workflow,
        AdminActionLogger $actionLogger,
    ): RedirectResponse {
        $request->validate([
            'trang_thai' => 'required|in:cho_xu_ly,dang_xu_ly,dang_giao,hoan_thanh,da_huy',
            'ghi_chu' => 'nullable|string|max:500',
        ]);

        try {
            $workflow->transition(
                $id,
                $request->trang_thai,
                Auth::id(),
                $request->ghi_chu ?? "Nhân viên chuyển trạng thái đơn sang {$request->trang_thai}",
                Auth::user()?->hasRole('admin') ? 'admin' : 'staff',
            );
            $actionLogger->log($request, 'order.status.updated', 'don_hang', $id, [
                'trang_thai_moi' => $request->trang_thai,
                'ghi_chu' => $request->ghi_chu,
            ]);

            return redirect()->back()->with('success', 'Cập nhật trạng thái đơn hàng thành công!');
        } catch (LogicException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    public function updateShipping(Request $request, int $id, AdminActionLogger $actionLogger): RedirectResponse
    {
        $data = $request->validate([
            'don_vi_van_chuyen' => ['required', 'in:GHN,GHTK'],
            'ma_van_don' => ['required', 'string', 'max:100'],
            'tracking_url' => ['nullable', 'url', 'max:500'],
        ]);

        $order = DonHang::findOrFail($id);
        $order->update($data);
        $actionLogger->log($request, 'order.shipping.updated', 'don_hang', $order->id, $data);

        return redirect()->back()->with('success', 'Thông tin vận chuyển đã được cập nhật.');
    }

    public function confirmBankTransfer(
        Request $request,
        int $id,
        OrderWorkflowService $workflow,
        AdminActionLogger $actionLogger,
    ): RedirectResponse {
        $data = $request->validate([
            'so_tien' => ['required', 'integer', 'min:1'],
            'ma_giao_dich' => ['required', 'string', 'max:100'],
            'signature' => ['required', 'regex:/^[a-f0-9]{64}$/i'],
        ]);

        $order = DonHang::with('thanhToan')->findOrFail($id);

        if ($order->thanhToan?->phuong_thuc_thanh_toan !== 'BankTransfer'
            || (int) $order->thanhToan->so_tien !== (int) $data['so_tien']
            || ! VietQRService::verifySignature($order->ma_don_hang, (int) $data['so_tien'], $data['signature'])) {
            return redirect()->back()->with('error', 'Số tiền hoặc chữ ký VietQR không khớp với đơn hàng.');
        }

        $result = $workflow->confirmPayment(
            $order->ma_don_hang,
            (int) $data['so_tien'],
            $data['ma_giao_dich'],
            'bank_transfer',
        );

        if (! in_array($result, ['paid', 'already_paid'], true)) {
            return redirect()->back()->with('error', 'Không thể xác nhận giao dịch chuyển khoản này.');
        }

        if ($result === 'paid') {
            $actionLogger->log($request, 'payment.bank_transfer.confirmed', 'don_hang', $order->id, [
                'so_tien' => (int) $data['so_tien'],
                'ma_giao_dich' => $data['ma_giao_dich'],
            ]);
        }

        return redirect()->back()->with('success', 'Đã đối soát và xác nhận chuyển khoản.');
    }
}
