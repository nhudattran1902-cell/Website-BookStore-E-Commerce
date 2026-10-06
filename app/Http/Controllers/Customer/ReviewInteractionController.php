<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DanhGiaSach;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewInteractionController extends Controller
{
    public function toggleLike(int $reviewId): JsonResponse
    {
        $liked = DB::transaction(function () use ($reviewId): bool {
            $review = DanhGiaSach::where('da_duyet', true)
                ->lockForUpdate()
                ->findOrFail($reviewId);

            $existingLike = $review->luotThich()
                ->where('user_id', Auth::id())
                ->first();

            if ($existingLike) {
                $existingLike->delete();

                return false;
            }

            $review->luotThich()->create(['user_id' => Auth::id()]);

            return true;
        });

        return response()->json([
            'liked' => $liked,
            'count' => DanhGiaSach::findOrFail($reviewId)->luotThich()->count(),
        ]);
    }

    public function storeReply(Request $request, int $reviewId): JsonResponse
    {
        $validated = $request->validate([
            'noi_dung' => ['required', 'string', 'max:1000'],
        ]);

        $review = DanhGiaSach::where('da_duyet', true)->findOrFail($reviewId);
        $reply = $review->binhLuans()->create([
            'user_id' => Auth::id(),
            'noi_dung' => trim($validated['noi_dung']),
        ]);
        $reply->load('nguoiDung');

        return response()->json([
            'reply' => [
                'id' => $reply->id,
                'user_name' => $reply->nguoiDung?->ho_ten ?? 'Khách hàng',
                'noi_dung' => $reply->noi_dung,
                'ngay_tao' => $reply->ngay_tao,
            ],
        ], 201);
    }
}
