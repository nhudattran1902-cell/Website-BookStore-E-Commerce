<?php

namespace App\Services;

use App\Models\KhoHang;
use App\Models\NguoiDung;
use App\Models\Sach;
use App\Models\TheoDoiHang;
use App\Notifications\BookAvailableNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class StockAvailabilityNotifier
{
    public function notifyIfAvailable(int $bookId): void
    {
        $recipients = DB::transaction(function () use ($bookId): Collection {
            $stock = KhoHang::where('id_sach', $bookId)->lockForUpdate()->first();

            if (! $stock || $stock->so_luong_kha_dung < 1) {
                return collect();
            }

            $subscriptions = TheoDoiHang::query()
                ->where('id_sach', $bookId)
                ->whereNull('da_thong_bao_at')
                ->whereHas('nguoiDung', fn ($query) => $query->where('dang_hoat_dong', true))
                ->with('nguoiDung')
                ->lockForUpdate()
                ->get();

            if ($subscriptions->isEmpty()) {
                return collect();
            }

            TheoDoiHang::whereIn('id', $subscriptions->modelKeys())
                ->whereNull('da_thong_bao_at')
                ->update([
                    'da_thong_bao_at' => now(),
                    'ngay_cap_nhat' => now(),
                ]);

            return $subscriptions->pluck('nguoiDung')->filter();
        }, attempts: 3);

        if ($recipients->isEmpty()) {
            return;
        }

        $book = Sach::find($bookId);

        if (! $book) {
            return;
        }

        foreach ($recipients as $recipient) {
            if (! $recipient instanceof NguoiDung) {
                continue;
            }

            try {
                $recipient->notify(new BookAvailableNotification($book));
            } catch (Throwable $exception) {
                Log::warning('Could not deliver a book availability notification.', [
                    'user_id' => $recipient->id,
                    'book_id' => $book->id,
                    'exception' => $exception::class,
                ]);
            }
        }
    }
}
