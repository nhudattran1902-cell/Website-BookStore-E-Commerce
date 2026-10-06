<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Services\OrderWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LogicException;

class ShippingWebhookController extends Controller
{
    /**
     * Webhook tiếp nhận trạng thái vận chuyển tự động từ GHN/GHTK.
     */
    public function handleStatusUpdate(Request $request, string $provider, OrderWorkflowService $workflow): JsonResponse
    {
        $provider = strtolower($provider);
        $secret = config("services.shipping.{$provider}.webhook_secret");
        $providedSignature = $request->header('X-Webhook-Signature', '');

        if (! is_string($secret) || $secret === '') {
            return response()->json(['success' => false, 'message' => 'Webhook chưa được cấu hình'], 503);
        }

        $providedSignature = preg_replace('/^sha256=/i', '', $providedSignature) ?? '';
        $expectedSignature = hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expectedSignature, $providedSignature)) {
            return response()->json(['success' => false, 'message' => 'Chữ ký webhook không hợp lệ'], 401);
        }

        $data = $request->validate([
            'order_code' => ['nullable', 'string', 'max:100'],
            'label_id' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:80'],
            'status_code' => ['nullable', 'string', 'max:80'],
            'event_id' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:255'],
            'current_location' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $trackingNumber = $data['order_code'] ?? $data['label_id'] ?? $data['tracking_number'] ?? null;
        $providerStatus = strtolower($data['status'] ?? $data['status_code'] ?? '');

        if (! $trackingNumber || ! $providerStatus) {
            return response()->json(['success' => false, 'message' => 'Thiếu mã vận đơn hoặc trạng thái'], 422);
        }

        $order = DonHang::where('ma_van_don', $trackingNumber)
            ->where('don_vi_van_chuyen', strtoupper($provider))
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy vận đơn'], 404);
        }

        $statusMap = [
            'ready_to_pick' => 'dang_xu_ly',
            'delivering' => 'dang_giao',
            'delivery' => 'dang_giao',
            'delivered' => 'hoan_thanh',
            'cancel' => 'da_huy',
            'cancelled' => 'da_huy',
        ];
        $eventId = $data['event_id'] ?? hash('sha256', $request->getContent());
        $location = $data['location'] ?? $data['current_location'] ?? null;
        $note = $data['note'] ?? "Cập nhật vận chuyển: {$providerStatus}";
        $source = 'shipping_'.$provider;

        try {
            $accepted = $workflow->recordCarrierEvent(
                $order,
                $source,
                $eventId,
                $note,
                $location,
                $statusMap[$providerStatus] ?? null,
            );
        } catch (LogicException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 409);
        }

        return response()->json([
            'success' => true,
            'duplicate' => ! $accepted,
            'message' => 'Đã nhận sự kiện vận chuyển.',
        ]);
    }
}
