<?php

namespace App\Http\Controllers;

use App\Models\Sach;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookController extends Controller
{
    /** Hiển thị sách theo chủ đề, bộ truyện và tập. */
    public function index(Request $request): View
    {
        $query = Sach::with(['tacGia', 'khoHang', 'theLoai'])
            ->where('dang_hoat_dong', true);

        if ($request->filled('the_loai')) {
            $query->where('id_the_loai', $request->the_loai);
        }

        $sort = $request->input('sort');
        if ($sort === 'price_asc') {
            $query->orderBy('gia_ban', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('gia_ban', 'desc');
        } else {
            $query->orderBy('tieu_de');
        }

        $books = $query->get();
        $topics = $books
            ->groupBy(fn (Sach $book): string => (string) ($book->id_the_loai ?? 'uncategorized'))
            ->map(function (Collection $topicBooks): array {
                /** @var Sach $firstBook */
                $firstBook = $topicBooks->first();
                $series = $topicBooks
                    ->groupBy(function (Sach $book): string {
                        $details = $this->seriesDetails($book->tieu_de);
                        $book->setAttribute('catalog_series_name', $details['name']);
                        $book->setAttribute('catalog_volume_label', $details['volume_label']);
                        $book->setAttribute('catalog_volume_order', $details['volume_order']);

                        return $details['name'];
                    })
                    ->map(function (Collection $volumes): array {
                        return [
                            'name' => $volumes->first()->getAttribute('catalog_series_name'),
                            'volumes' => $volumes
                                ->sortBy(fn (Sach $book): array => [
                                    $book->getAttribute('catalog_volume_order'),
                                    $book->getAttribute('catalog_volume_label'),
                                    $book->id,
                                ])
                                ->values(),
                        ];
                    })
                    ->values();

                return [
                    'id' => $firstBook->id_the_loai ?? 'uncategorized',
                    'name' => $firstBook->theLoai?->ten_the_loai ?? 'Chưa phân loại',
                    'book_count' => $topicBooks->count(),
                    'series' => $series,
                ];
            })
            ->values();

        return view('books.catalog', compact('topics'));
    }

    /**
     * @return array{name: string, volume_label: string, volume_order: int}
     */
    private function seriesDetails(string $title): array
    {
        $pattern = '/^(?<series>.+?)\s*-\s*(?:Tập\s*-\s*)?(?<type>Tập|Phần|Môn|Bộ)\s*(?<number>\d+)(?:\s*:\s*(?<subtitle>.*))?$/iu';

        if (! preg_match($pattern, $title, $matches)) {
            return [
                'name' => 'Sách lẻ',
                'volume_label' => $title,
                'volume_order' => 1,
            ];
        }

        $subtitle = trim($matches['subtitle'] ?? '');

        return [
            'name' => trim($matches['series']),
            'volume_label' => trim($matches['type'].' '.$matches['number'].($subtitle !== '' ? ': '.$subtitle : '')),
            'volume_order' => (int) $matches['number'],
        ];
    }

    /**
     * Hiển thị chi tiết cuốn sách (kèm tác giả, trang đọc thử, đánh giá đã duyệt, điểm sao, sách liên quan & sách đã xem)
     */
    public function show(int $id): View
    {
        // 1. Lấy thông tin sách cùng các quan hệ liên quan
        $book = Sach::with(['theLoai', 'nhaXuatBan', 'tacGia', 'khoHang', 'trangSach'])
            ->where('dang_hoat_dong', true)
            ->findOrFail($id);

        $isFollowingStock = Auth::check()
            && Auth::user()->theoDoiHang()
                ->where('id_sach', $book->id)
                ->whereNull('da_thong_bao_at')
                ->exists();

        // 2. XỬ LÝ SESSION SÁCH ĐÃ XEM (RECENTLY VIEWED)
        $recentlyViewedIds = session()->get('recently_viewed_books', []);

        // Loại bỏ ID sách hiện tại nếu đã tồn tại trước đó để đưa lên đầu danh sách
        $recentlyViewedIds = array_diff($recentlyViewedIds, [$book->id]);

        // Thêm ID sách hiện tại vào đầu danh sách
        array_unshift($recentlyViewedIds, $book->id);

        // Giới hạn lưu tối đa 10 ID sách gần đây trong session
        $recentlyViewedIds = array_slice($recentlyViewedIds, 0, 10);

        // Cập nhật lại danh sách ID vào Session
        session()->put('recently_viewed_books', $recentlyViewedIds);

        // 3. TRUY VẤN DANH SÁCH SÁCH ĐÃ XEM ĐỂ HIỂN THỊ (Loại trừ sách đang xem hiện tại)
        $idsToFetch = array_diff($recentlyViewedIds, [$book->id]);

        $recentlyViewedBooks = collect();
        if (! empty($idsToFetch)) {
            $recentlyViewedBooks = Sach::with(['tacGia', 'theLoai', 'khoHang'])
                ->where('dang_hoat_dong', true)
                ->whereIn('id', $idsToFetch)
                ->get()
                // Giữ nguyên thứ tự xem gần đây nhất từ mảng Session
                ->sortBy(fn ($item) => array_search($item->id, $idsToFetch));
        }

        // 4. Lấy danh sách tác giả (alias cho view cũ)
        $authors = $book->tacGia;

        // 5. Lấy các trang sách đọc thử
        $previewPages = $book->trangSach()
            ->where('cho_phep_doc_thu', true)
            ->orderBy('so_trang', 'asc')
            ->get();

        // 6. Lấy danh sách đánh giá đã duyệt (da_duyet = true) kèm người dùng
        $reviews = $book->danhGia()
            ->with([
                'nguoiDung',
                'binhLuans.nguoiDung',
                'luotThich' => fn ($query) => $query->where('user_id', auth()->id()),
            ])
            ->withCount(['binhLuans', 'luotThich'])
            ->where('da_duyet', true)
            ->latest('ngay_tao')
            ->paginate(5);

        // 7. Tính điểm sao trung bình và tổng lượt đánh giá
        $avgRating = round((float) $book->danhGia()->where('da_duyet', true)->avg('so_sao'), 1);
        $totalReviews = $book->danhGia()->where('da_duyet', true)->count();

        // 8. Lấy danh sách sách liên quan (cùng thể loại, loại trừ sách hiện tại, ngẫu nhiên 4 cuốn)
        $relatedBooks = Sach::with(['tacGia', 'theLoai', 'khoHang'])
            ->where('dang_hoat_dong', true)
            ->where('id_the_loai', $book->id_the_loai)
            ->where('id', '!=', $book->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('books.show', compact(
            'book',
            'authors',
            'previewPages',
            'reviews',
            'avgRating',
            'totalReviews',
            'relatedBooks',
            'recentlyViewedBooks',
            'isFollowingStock',
        ));
    }

    /** Show books matching the search phrase across searchable catalog fields. */
    public function search(Request $request): View|RedirectResponse
    {
        $keyword = trim((string) ($request->input('q') ?? $request->input('keyword', '')));

        if ($keyword === '') {
            return redirect()->route('books.index');
        }

        $books = $this->matchingBooks($keyword)
            ->with(['theLoai', 'tacGia', 'nhaXuatBan'])
            ->orderByRaw('CASE WHEN sach.tieu_de = ? THEN 0 WHEN sach.tieu_de LIKE ? THEN 1 ELSE 2 END', [
                $keyword,
                $keyword.'%',
            ])
            ->orderBy('sach.tieu_de')
            ->paginate(12)
            ->withQueryString();

        return view('books.search', compact('books', 'keyword'));
    }

    /** Return a small list of suggestions for the storefront search box. */
    public function suggestions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keyword' => ['nullable', 'string', 'max:100'],
        ]);
        $keyword = trim((string) ($validated['keyword'] ?? ''));

        if (Str::length($keyword) < 2) {
            return response()->json([]);
        }

        $suggestions = $this->matchingBooks($keyword)
            ->with(['tacGia:id,ten_tac_gia', 'nhaXuatBan:id,ten_nxb'])
            ->orderByRaw('CASE WHEN sach.tieu_de LIKE ? THEN 0 ELSE 1 END', [$keyword.'%'])
            ->orderBy('sach.tieu_de')
            ->limit(8)
            ->get(['sach.id', 'sach.id_nha_xuat_ban', 'sach.tieu_de', 'sach.ma_isbn', 'sach.anh_bia'])
            ->map(fn (Sach $book): array => [
                'id' => $book->id,
                'title' => $book->tieu_de,
                'authors' => $book->tacGia->pluck('ten_tac_gia')->implode(', '),
                'publisher' => $book->nhaXuatBan?->ten_nxb,
                'isbn' => $book->ma_isbn,
                'cover' => $book->anh_bia_url,
                'url' => route('books.show', $book->id),
            ])
            ->values();

        return response()->json($suggestions);
    }

    private function matchingBooks(string $keyword): Builder
    {
        $pattern = '%'.$keyword.'%';
        $supportsFullText = DB::connection()->getDriverName() === 'mysql';

        return Sach::query()
            ->where('sach.dang_hoat_dong', true)
            ->where(function (Builder $query) use ($keyword, $pattern, $supportsFullText): void {
                $query->where(function (Builder $bookQuery) use ($keyword, $pattern, $supportsFullText): void {
                    if ($supportsFullText) {
                        $bookQuery->whereFullText(['tieu_de', 'mo_ta'], $keyword)
                            ->orWhere('sach.tieu_de', 'like', $pattern);
                    } else {
                        $bookQuery->where('sach.tieu_de', 'like', $pattern)
                            ->orWhere('sach.mo_ta', 'like', $pattern);
                    }
                })
                    ->orWhere('sach.ma_isbn', 'like', $pattern)
                    ->orWhereHas('tacGia', function (Builder $authorQuery) use ($keyword, $pattern, $supportsFullText): void {
                        if ($supportsFullText) {
                            $authorQuery->whereFullText('ten_tac_gia', $keyword)
                                ->orWhere('ten_tac_gia', 'like', $pattern);
                        } else {
                            $authorQuery->where('ten_tac_gia', 'like', $pattern);
                        }
                    })
                    ->orWhereHas('nhaXuatBan', function (Builder $publisherQuery) use ($keyword, $pattern, $supportsFullText): void {
                        if ($supportsFullText) {
                            $publisherQuery->whereFullText('ten_nxb', $keyword)
                                ->orWhere('ten_nxb', 'like', $pattern);
                        } else {
                            $publisherQuery->where('ten_nxb', 'like', $pattern);
                        }
                    })
                    ->orWhereHas('theLoai', fn (Builder $categoryQuery) => $categoryQuery->where('ten_the_loai', 'like', $pattern));
            });
    }
}
