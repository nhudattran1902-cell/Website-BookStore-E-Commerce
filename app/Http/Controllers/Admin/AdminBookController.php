<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoHang;
use App\Models\NhaXuatBan;
use App\Models\Sach;
use App\Models\TheLoai;
use App\Services\StockAvailabilityNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdminBookController extends Controller
{
    // Hiển thị danh sách sách (index.blade.php)
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'the_loai' => ['nullable', 'integer', Rule::exists('the_loai', 'id')],
            'gia_tu' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'gia_den' => ['nullable', 'numeric', 'min:0', 'max:999999999999', 'gte:gia_tu'],
            'ton_kho' => ['nullable', 'in:available,low,out'],
            'trang_thai' => ['nullable', 'in:active,inactive'],
            'sort_gia' => ['nullable', 'in:asc,desc'],
        ]);

        $books = Sach::with(['khoHang', 'theLoai', 'tacGia'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where('tieu_de', 'like', '%'.$search.'%');
            })
            ->when($filters['the_loai'] ?? null, function ($query, int $categoryId): void {
                $query->where('id_the_loai', $categoryId);
            })
            ->when($filters['gia_tu'] ?? null, function ($query, float $price): void {
                $query->where('gia_ban', '>=', $price);
            })
            ->when($filters['gia_den'] ?? null, function ($query, float $price): void {
                $query->where('gia_ban', '<=', $price);
            })
            ->when($filters['ton_kho'] ?? null, function ($query, string $stock): void {
                if ($stock === 'available') {
                    $query->whereHas('khoHang', function ($stockQuery): void {
                        $stockQuery->whereRaw('(so_luong_ton - so_luong_dat_truoc) > 0');
                    });
                }

                if ($stock === 'low') {
                    $query->whereHas('khoHang', function ($stockQuery): void {
                        $stockQuery
                            ->whereRaw('(so_luong_ton - so_luong_dat_truoc) > 0')
                            ->whereRaw(
                                '(so_luong_ton - so_luong_dat_truoc) <= COALESCE(nguong_canh_bao, 5)'
                            );
                    });
                }

                if ($stock === 'out') {
                    $query->where(function ($stockQuery): void {
                        $stockQuery
                            ->whereDoesntHave('khoHang')
                            ->orWhereHas('khoHang', function ($inventoryQuery): void {
                                $inventoryQuery->whereRaw(
                                    '(so_luong_ton - so_luong_dat_truoc) <= 0'
                                );
                            });
                    });
                }
            })
            ->when($filters['trang_thai'] ?? null, function ($query, string $status): void {
                $query->where('dang_hoat_dong', $status === 'active');
            })
            ->when($filters['sort_gia'] ?? null, function ($query, string $direction): void {
                $query->orderBy('gia_ban', $direction);
            }, function ($query): void {
                $query->latest('ngay_tao');
            })
            ->paginate(10)
            ->withQueryString();

        $categories = TheLoai::orderBy('ten_the_loai')->get();

        return view('admin.books.index', compact('books', 'categories'));
    }

    // Hiển thị form thêm mới sách (create.blade.php)
    public function create(): View
    {
        $categories = TheLoai::all();
        $publishers = NhaXuatBan::all();

        return view('admin.books.create', compact('categories', 'publishers'));
    }

    // Xử lý lưu sách mới vào CSDL nha_sach_db
    public function store(Request $request, StockAvailabilityNotifier $stockNotifier): RedirectResponse
    {
        $request->validate([
            'tieu_de' => 'required|max:255',
            'id_the_loai' => 'required|exists:the_loai,id',
            'gia_ban' => 'required|numeric',
            'gia_khuyen_mai' => 'nullable|numeric|gt:0|lt:gia_ban|max:999999999999',
            'so_luong_ton' => 'nullable|integer|min:0',
            'so_trang' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'loai_bia' => ['nullable', Rule::in(['bia_mem', 'bia_cung'])],
            'khoi_luong_gram' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'chieu_rong_mm' => ['nullable', 'numeric', 'gt:0', 'max:5000'],
            'chieu_cao_mm' => ['nullable', 'numeric', 'gt:0', 'max:5000'],
            'do_day_mm' => ['nullable', 'numeric', 'gt:0', 'max:1000'],
            'ngon_ngu' => ['nullable', 'string', 'max:100'],
            'lan_tai_ban' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'nha_cung_cap' => ['nullable', 'string', 'max:255'],
            'anh_bia' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:max_width=2000,max_height=3000',
        ], [
            'anh_bia.dimensions' => 'Kích thước ảnh tối đa là 2000 x 3000 pixel.',
        ]);

        $data = $request->except(['_token', 'so_luong_ton']);

        // Gán trạng thái hiển thị
        $data['dang_hoat_dong'] = $request->has('dang_hoat_dong') ? 1 : 0;

        // Tự động tạo slug từ tiêu đề sách nếu thiếu
        if (empty($data['duong_dan_tinh'])) {
            $data['duong_dan_tinh'] = Str::slug($request->tieu_de).'-'.time();
        }

        // Xử lý lưu ảnh bìa sách
        if ($request->hasFile('anh_bia')) {
            $path = $request->file('anh_bia')->store('books', 'public');
            $data['anh_bia'] = $path;
        }

        $book = Sach::create($data);

        // Tạo bản ghi tồn kho tương ứng
        KhoHang::create([
            'id_sach' => $book->id,
            'so_luong_ton' => $request->input('so_luong_ton', 0),
            'so_luong_dat_truoc' => 0,
        ]);

        $stockNotifier->notifyIfAvailable((int) $book->id);

        return redirect()->route('admin.books.index')->with('success', 'Thêm sách mới thành công!');
    }

    // Hiển thị form chỉnh sửa sách
    public function edit(int $id): View
    {
        $book = Sach::with('khoHang')->findOrFail($id);
        $categories = TheLoai::all();
        $publishers = NhaXuatBan::all();

        return view('admin.books.edit', compact('book', 'categories', 'publishers'));
    }

    // Cập nhật thông tin sách
    public function update(Request $request, int $id, StockAvailabilityNotifier $stockNotifier): RedirectResponse
    {
        $request->validate([
            'tieu_de' => 'required|max:255',
            'id_the_loai' => 'required|exists:the_loai,id',
            'gia_ban' => 'required|numeric',
            'gia_khuyen_mai' => 'nullable|numeric|gt:0|lt:gia_ban|max:999999999999',
            'so_luong_ton' => 'nullable|integer|min:0',
            'so_trang' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'loai_bia' => ['nullable', Rule::in(['bia_mem', 'bia_cung'])],
            'khoi_luong_gram' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'chieu_rong_mm' => ['nullable', 'numeric', 'gt:0', 'max:5000'],
            'chieu_cao_mm' => ['nullable', 'numeric', 'gt:0', 'max:5000'],
            'do_day_mm' => ['nullable', 'numeric', 'gt:0', 'max:1000'],
            'ngon_ngu' => ['nullable', 'string', 'max:100'],
            'lan_tai_ban' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'nha_cung_cap' => ['nullable', 'string', 'max:255'],
            'anh_bia' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048|dimensions:max_width=2000,max_height=3000',
        ], [
            'anh_bia.dimensions' => 'Kích thước ảnh tối đa là 2000 x 3000 pixel.',
        ]);

        $book = Sach::findOrFail($id);
        $data = $request->except(['_token', '_method', 'so_luong_ton']);
        unset($data['anh_bia']);

        $data['dang_hoat_dong'] = $request->has('dang_hoat_dong') ? 1 : 0;

        if (empty($data['duong_dan_tinh'])) {
            $data['duong_dan_tinh'] = Str::slug($request->tieu_de).'-'.$book->id;
        }

        $newCoverPath = null;

        if ($request->hasFile('anh_bia')) {
            $newCoverPath = $request->file('anh_bia')->store('books', 'public');
            $data['anh_bia'] = $newCoverPath;
        }

        $oldCoverPath = null;

        try {
            $book = DB::transaction(function () use (
                $id,
                $request,
                $data,
                &$oldCoverPath
            ): Sach {
                $book = Sach::whereKey($id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $stock = null;
                $newQuantity = null;

                if ($request->has('so_luong_ton')) {
                    $newQuantity = (int) $request->input('so_luong_ton');

                    $stock = KhoHang::where('id_sach', $book->id)
                        ->lockForUpdate()
                        ->first();

                    $reservedQuantity = (int) ($stock?->so_luong_dat_truoc ?? 0);

                    if ($newQuantity < $reservedQuantity) {
                        throw ValidationException::withMessages([
                            'so_luong_ton' => 'Số tồn không thể thấp hơn số lượng đang giữ cho đơn hàng.',
                        ]);
                    }
                }

                $oldCoverPath = $book->anh_bia;
                $book->update($data);

                if ($request->has('so_luong_ton')) {
                    if ($stock) {
                        $stock->update([
                            'so_luong_ton' => $newQuantity,
                        ]);
                    } else {
                        KhoHang::create([
                            'id_sach' => $book->id,
                            'so_luong_ton' => $newQuantity,
                            'so_luong_dat_truoc' => 0,
                            'nguong_canh_bao' => 5,
                        ]);
                    }
                }

                return $book;
            });
        } catch (Throwable $exception) {
            if ($newCoverPath !== null) {
                Storage::disk('public')->delete($newCoverPath);
            }

            throw $exception;
        }

        if (
            $newCoverPath !== null
            && $oldCoverPath
            && $oldCoverPath !== $newCoverPath
            && Storage::disk('public')->exists($oldCoverPath)
        ) {
            Storage::disk('public')->delete($oldCoverPath);
        }

        if ($request->has('so_luong_ton')) {
            $stockNotifier->notifyIfAvailable((int) $book->id);
        }

        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Cập nhật thông tin sách thành công!');
    }

    // Xóa sách
    public function destroy(int $id): RedirectResponse
    {
        $book = Sach::findOrFail($id);

        // Xóa ảnh bìa trong storage khi xóa sách
        if ($book->anh_bia && Storage::disk('public')->exists($book->anh_bia)) {
            Storage::disk('public')->delete($book->anh_bia);
        }

        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Xóa sách thành công!');
    }
}
