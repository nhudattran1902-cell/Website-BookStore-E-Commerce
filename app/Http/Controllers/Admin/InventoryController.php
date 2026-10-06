<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoHang;
use App\Models\Sach;
use App\Models\TheLoai;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    /**
     * Danh sách kho hàng, tự động khởi tạo bản ghi kho nếu chưa có,
     * hỗ trợ đầy đủ các bộ lọc tìm kiếm và phân trang.
     */
    public function index(Request $request): View
    {
        // 1. Tự động kiểm tra và khởi tạo bản ghi kho cho tất cả sách chưa có
        $sachChuaCoKho = Sach::whereDoesntHave('khoHang')->get();
        foreach ($sachChuaCoKho as $sach) {
            KhoHang::firstOrCreate(
                ['id_sach' => $sach->id],
                [
                    'so_luong_ton' => 0,
                    'nguong_canh_bao' => 5,
                ]
            );
        }

        // 2. Validate dữ liệu đầu vào của bộ lọc
        $filters = $request->validate([
            'id_sach' => ['nullable', 'integer', Rule::exists('sach', 'id')],
            'search' => ['nullable', 'string', 'max:255'],
            'the_loai' => ['nullable', 'integer', Rule::exists('the_loai', 'id')],
            'gia_tu' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'gia_den' => ['nullable', 'numeric', 'min:0', 'max:999999999999', 'gte:gia_tu'],
            'ton_kho' => ['nullable', 'in:available,low,out'],
            'trang_thai' => ['nullable', 'in:active,inactive'],
            'sort_gia' => ['nullable', 'in:asc,desc'],
        ]);

        // 3. Xây dựng Query tìm kiếm & lọc dữ liệu
        $query = KhoHang::with(['sach.theLoai'])
            ->when($filters['id_sach'] ?? null, function ($query, int $bookId): void {
                $query->where('id_sach', $bookId);
            })
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->whereHas('sach', function ($bookQuery) use ($search): void {
                    $bookQuery->where('tieu_de', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['the_loai'] ?? null, function ($query, int $categoryId): void {
                $query->whereHas('sach', function ($bookQuery) use ($categoryId): void {
                    $bookQuery->where('id_the_loai', $categoryId);
                });
            })
            ->when($filters['gia_tu'] ?? null, function ($query, float $price): void {
                $query->whereHas('sach', function ($bookQuery) use ($price): void {
                    $bookQuery->where('gia_ban', '>=', $price);
                });
            })
            ->when($filters['gia_den'] ?? null, function ($query, float $price): void {
                $query->whereHas('sach', function ($bookQuery) use ($price): void {
                    $bookQuery->where('gia_ban', '<=', $price);
                });
            })
            ->when($filters['ton_kho'] ?? null, function ($query, string $stock): void {
                if ($stock === 'available') {
                    $query->whereRaw(
                        '(so_luong_ton - so_luong_dat_truoc) > 0'
                    );
                }

                if ($stock === 'low') {
                    $query
                        ->whereRaw('(so_luong_ton - so_luong_dat_truoc) > 0')
                        ->whereRaw(
                            '(so_luong_ton - so_luong_dat_truoc) <= COALESCE(nguong_canh_bao, 5)'
                        );
                }

                if ($stock === 'out') {
                    $query->whereRaw(
                        '(so_luong_ton - so_luong_dat_truoc) <= 0'
                    );
                }
            })
            ->when($filters['trang_thai'] ?? null, function ($query, string $status): void {
                $query->whereHas('sach', function ($bookQuery) use ($status): void {
                    $bookQuery->where('dang_hoat_dong', $status === 'active');
                });
            });

        // Sắp xếp theo giá bán sách hoặc số lượng tồn kho mặc định
        if (($filters['sort_gia'] ?? null) !== null) {
            $query->leftJoin('sach', 'sach.id', '=', 'kho_hang.id_sach')
                ->select('kho_hang.*')
                ->orderBy('sach.gia_ban', $filters['sort_gia']);
        } else {
            $query->orderBy('so_luong_ton', 'asc');
        }

        $inventory = $query->paginate(20)->withQueryString();
        $categories = TheLoai::orderBy('ten_the_loai')->get();

        return view('admin.inventory.index', compact('inventory', 'categories'));
    }

    /**
     * Cập nhật số lượng tồn kho, nhập hàng nhanh hoặc điều chỉnh vị trí kho (Khu vực/Kệ/Ô).
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'so_luong_ton' => ['nullable', 'integer', 'min:0'],
            'so_luong_nhap' => ['nullable', 'integer', 'min:1'],
            'nguong_canh_bao' => ['nullable', 'integer', 'min:0'],
            'khu_vuc' => ['nullable', 'string', 'max:50'],
            'ke_hang' => ['nullable', 'string', 'max:50'],
            'o_chua' => ['nullable', 'string', 'max:50'],
        ]);

        $isReceivingStock = $request->filled('so_luong_nhap');

        $stock = DB::transaction(function () use ($request, $id, $isReceivingStock): KhoHang {
            $stock = KhoHang::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($isReceivingStock) {
                $stock->so_luong_ton += (int) $request->input('so_luong_nhap');

                if ($request->filled('nguong_canh_bao')) {
                    $stock->nguong_canh_bao = $request->input('nguong_canh_bao');
                }

                if ($request->has('khu_vuc')) {
                    $stock->khu_vuc = $request->input('khu_vuc');
                }

                if ($request->has('ke_hang')) {
                    $stock->ke_hang = $request->input('ke_hang');
                }

                if ($request->has('o_chua')) {
                    $stock->o_chua = $request->input('o_chua');
                }

                $stock->save();

                return $stock;
            }

            $newQuantity = $request->filled('so_luong_ton')
                ? (int) $request->input('so_luong_ton')
                : (int) $stock->so_luong_ton;

            if ($newQuantity < (int) $stock->so_luong_dat_truoc) {
                throw ValidationException::withMessages([
                    'so_luong_ton' => 'Số tồn không thể thấp hơn số lượng đang giữ cho đơn hàng.',
                ]);
            }

            $stock->update([
                'so_luong_ton' => $newQuantity,
                'nguong_canh_bao' => $request->input('nguong_canh_bao') ?? $stock->nguong_canh_bao,
                'khu_vuc' => $request->has('khu_vuc') ? $request->input('khu_vuc') : $stock->khu_vuc,
                'ke_hang' => $request->has('ke_hang') ? $request->input('ke_hang') : $stock->ke_hang,
                'o_chua' => $request->has('o_chua') ? $request->input('o_chua') : $stock->o_chua,
            ]);

            return $stock;
        });

        if ($isReceivingStock) {
            return redirect()->back()->with(
                'success',
                "Đã nhập thành công {$request->input('so_luong_nhap')} cuốn cho sách: {$stock->sach->tieu_de}"
            );
        }

        return redirect()->back()->with(
            'success',
            "Đã cập nhật cấu hình kho cho sách: {$stock->sach->tieu_de}"
        );
    }
}
