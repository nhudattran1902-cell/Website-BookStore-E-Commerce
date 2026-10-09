<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChiTietPhieuNhap;
use App\Models\KhoHang;
use App\Models\NhaXuatBan;
use App\Models\PhieuNhapKho;
use App\Models\Sach;
use App\Services\AdminActionLogger;
use App\Services\StockAvailabilityNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InventoryImportController extends Controller
{
    public function index(): View
    {
        $importReceipts = PhieuNhapKho::with(['nhaXuatBan', 'nguoiNhap', 'chiTietPhieuNhap.sach'])
            ->latest('ngay_tao')
            ->paginate(15);

        return view('admin.inventory.import_index', compact('importReceipts'));
    }

    public function create(): View
    {
        $publishers = NhaXuatBan::orderBy('ten_nxb')->get();
        $books = Sach::where('dang_hoat_dong', true)->orderBy('tieu_de')->get();

        return view('admin.inventory.import_create', compact('publishers', 'books'));
    }

    public function store(
        Request $request,
        StockAvailabilityNotifier $stockNotifier,
        AdminActionLogger $actionLogger,
    ): RedirectResponse {
        $data = $request->validate([
            'id_nha_xuat_ban' => ['nullable', 'exists:nha_xuat_ban,id'],
            'ghi_chu' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_sach' => ['required', 'exists:sach,id'],
            'items.*.so_luong' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.don_gia_nhap' => ['required', 'numeric', 'min:0', 'max:999999999999'],
        ], [
            'items.required' => 'Vui lòng chọn ít nhất một cuốn sách để nhập kho.',
        ]);

        $receipt = DB::transaction(function () use ($data): PhieuNhapKho {
            $total = collect($data['items'])->sum(
                fn (array $item): float => $item['so_luong'] * $item['don_gia_nhap'],
            );

            $receipt = PhieuNhapKho::create([
                'ma_phieu' => 'PNK-'.strtoupper(Str::random(8)),
                'id_nha_xuat_ban' => $data['id_nha_xuat_ban'] ?? null,
                'id_nguoi_nhap' => Auth::id(),
                'tong_tien' => $total,
                'ghi_chu' => $data['ghi_chu'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                ChiTietPhieuNhap::create([
                    'id_phieu_nhap' => $receipt->id,
                    'id_sach' => $item['id_sach'],
                    'so_luong' => $item['so_luong'],
                    'don_gia_nhap' => $item['don_gia_nhap'],
                    'thanh_tien' => $item['so_luong'] * $item['don_gia_nhap'],
                ]);
            }

            foreach (collect($data['items'])->groupBy('id_sach') as $bookId => $bookItems) {
                $book = Sach::whereKey($bookId)->lockForUpdate()->firstOrFail();
                $stock = KhoHang::where('id_sach', $bookId)->lockForUpdate()->first();

                if (! $stock) {
                    $stock = KhoHang::create([
                        'id_sach' => $bookId,
                        'so_luong_ton' => 0,
                        'so_luong_dat_truoc' => 0,
                        'nguong_canh_bao' => 5,
                    ]);
                }

                $previousQuantity = $stock->so_luong_ton;
                $receivedQuantity = (int) $bookItems->sum('so_luong');
                $receivedCost = (float) $bookItems->sum(
                    fn (array $item): float => $item['so_luong'] * $item['don_gia_nhap'],
                );
                $combinedQuantity = $previousQuantity + $receivedQuantity;

                if ($combinedQuantity > 0) {
                    $book->gia_von = round((($book->gia_von * $previousQuantity) + $receivedCost) / $combinedQuantity);
                    $book->save();
                }

                $stock->increment('so_luong_ton', $receivedQuantity);
            }

            return $receipt;
        }, attempts: 3);

        $actionLogger->log($request, 'inventory.import_receipt.created', 'phieu_nhap_kho', $receipt->id, [
            'ma_phieu' => $receipt->ma_phieu,
            'tong_tien' => $receipt->tong_tien,
            'items' => collect($data['items'])->map(fn (array $item): array => [
                'id_sach' => $item['id_sach'],
                'so_luong' => $item['so_luong'],
                'don_gia_nhap' => $item['don_gia_nhap'],
            ])->values()->all(),
        ]);

        foreach (collect($data['items'])->pluck('id_sach')->unique() as $bookId) {
            $stockNotifier->notifyIfAvailable((int) $bookId);
        }

        return redirect()->route('admin.inventory.imports.index')
            ->with('success', "Tạo phiếu nhập kho {$receipt->ma_phieu} thành công!");
    }
}
