<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Services\OrderWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LogicException;

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách lịch sử đơn hàng của user đang đăng nhập (Kèm bộ lọc Tab trạng thái)
     */
    public function index(Request $request): View
    {
        $query = DonHang::with(['chiTietDonHang.sach', 'thanhToan'])
            ->where('id_nguoi_dung', Auth::id())
            ->orderBy('id', 'desc');

        // Lọc theo Tab trạng thái đơn hàng nếu người dùng chọn
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('trang_thai', $request->status);
        }

        $donHangs = $query->paginate(10)->withQueryString();

        // Tạo alias $orders và $danhSachDonHang để khớp với mọi file Blade View
        $orders = $donHangs;
        $danhSachDonHang = $donHangs;

        return view('customer.orders.index', compact('donHangs', 'orders', 'danhSachDonHang'));
    }

    /**
     * Xem chi tiết một đơn hàng cụ thể & Nhật ký tiến trình
     */
    public function show(int $id): View
    {
        // Query đơn hàng kèm các mối quan hệ chi tiết, thanh toán và nhật ký lịch sử
        $order = DonHang::with(['chiTietDonHang.sach', 'thanhToan', 'nguoiDung', 'lichSuDonHang.nguoiThayDoi'])
            ->where('id_nguoi_dung', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $donHang = $order;

        return view('customer.orders.show', compact('order', 'donHang'));
    }

    /**
     * Hủy đơn hàng khi ở trạng thái cho_xu_ly & Hoàn lại tồn kho tự động
     */
    public function cancel(int $id, OrderWorkflowService $workflow): RedirectResponse
    {
        $order = DonHang::query()
            ->where('id_nguoi_dung', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        try {
            $workflow->transition(
                $order->id,
                'da_huy',
                Auth::id(),
                'Khách hàng chủ động hủy đơn hàng',
                'customer',
            );

            return redirect()->back()->with('success', 'Hủy đơn hàng thành công và đã giải phóng số lượng giữ chỗ.');
        } catch (LogicException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Sửa Tên người nhận, SĐT và Địa chỉ giao hàng khi đơn chưa chuyển sang dang_giao
     */
    public function updateAddress(Request $request, int $id): RedirectResponse
    {
        $order = DonHang::where('id_nguoi_dung', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        if (in_array($order->trang_thai, ['dang_giao', 'hoan_thanh', 'da_huy'])) {
            return redirect()->back()->with('error', 'Không thể sửa thông tin khi đơn hàng đang giao, đã hoàn thành hoặc đã hủy!');
        }

        $request->validate([
            'ten_nguoi_nhan' => 'required|string|max:150',
            'sdt_nguoi_nhan' => 'required|string|max:20',
            'dia_chi_nhan' => 'required|string|max:500',
        ], [
            'ten_nguoi_nhan.required' => 'Vui lòng nhập tên người nhận.',
            'sdt_nguoi_nhan.required' => 'Vui lòng nhập số điện thoại.',
            'dia_chi_nhan.required' => 'Vui lòng nhập địa chỉ nhận hàng.',
        ]);

        $order->update([
            'ten_nguoi_nhan' => $request->ten_nguoi_nhan,
            'sdt_nguoi_nhan' => $request->sdt_nguoi_nhan,
            'dia_chi_nhan' => $request->dia_chi_nhan,
            'dia_chi_giao_hang' => $request->dia_chi_nhan,
        ]);

        return redirect()->back()->with('success', 'Cập nhật thông tin nhận hàng thành công!');
    }
}
