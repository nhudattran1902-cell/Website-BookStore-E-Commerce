<?php

namespace App\Services;

use App\Models\DonHang;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MoMoService
{
    public static function createPaymentUrl(DonHang $order): string
    {
        $partnerCode = config('services.momo.partner_code');
        $accessKey = config('services.momo.access_key');
        $secretKey = config('services.momo.secret_key');
        $endpoint = config('services.momo.endpoint');

        if (! $partnerCode || ! $accessKey || ! $secretKey || ! $endpoint) {
            throw new RuntimeException('MoMo chưa được cấu hình merchant credentials.');
        }

        $requestId = (string) Str::uuid();
        $payload = [
            'partnerCode' => $partnerCode,
            'requestId' => $requestId,
            'amount' => (string) (int) $order->thanh_tien,
            'orderId' => $order->ma_don_hang,
            'orderInfo' => 'Thanh toan don hang '.$order->ma_don_hang,
            'redirectUrl' => route('checkout.success', $order->ma_don_hang),
            'ipnUrl' => route('api.payments.momo.ipn'),
            'lang' => 'vi',
            'requestType' => 'captureWallet',
            'extraData' => '',
        ];

        $signatureData = implode('&', [
            'accessKey='.$accessKey,
            'amount='.$payload['amount'],
            'extraData='.$payload['extraData'],
            'ipnUrl='.$payload['ipnUrl'],
            'orderId='.$payload['orderId'],
            'orderInfo='.$payload['orderInfo'],
            'partnerCode='.$payload['partnerCode'],
            'redirectUrl='.$payload['redirectUrl'],
            'requestId='.$payload['requestId'],
            'requestType='.$payload['requestType'],
        ]);
        $payload['signature'] = hash_hmac('sha256', $signatureData, $secretKey);

        $response = Http::asJson()
            ->connectTimeout(3)
            ->timeout(10)
            ->post($endpoint, $payload)
            ->throw()
            ->json();

        if (($response['resultCode'] ?? null) !== 0 || empty($response['payUrl'])) {
            throw new RuntimeException('MoMo không tạo được yêu cầu thanh toán.');
        }

        return $response['payUrl'];
    }

    public static function verifyIpn(array $payload): bool
    {
        $accessKey = config('services.momo.access_key');
        $secretKey = config('services.momo.secret_key');
        $providedSignature = $payload['signature'] ?? null;

        if (! is_string($accessKey) || $accessKey === ''
            || ! is_string($secretKey) || $secretKey === ''
            || ! is_string($providedSignature)) {
            return false;
        }

        $keys = [
            'accessKey' => $accessKey,
            'amount' => $payload['amount'] ?? null,
            'extraData' => $payload['extraData'] ?? '',
            'message' => $payload['message'] ?? '',
            'orderId' => $payload['orderId'] ?? null,
            'orderInfo' => $payload['orderInfo'] ?? '',
            'orderType' => $payload['orderType'] ?? '',
            'partnerCode' => $payload['partnerCode'] ?? null,
            'payType' => $payload['payType'] ?? '',
            'requestId' => $payload['requestId'] ?? null,
            'responseTime' => $payload['responseTime'] ?? null,
            'resultCode' => $payload['resultCode'] ?? null,
            'transId' => $payload['transId'] ?? null,
        ];

        foreach ($keys as $value) {
            if (! is_scalar($value)) {
                return false;
            }
        }

        $signatureData = collect($keys)
            ->map(fn ($value, string $key): string => $key.'='.$value)
            ->implode('&');
        $expectedSignature = hash_hmac('sha256', $signatureData, $secretKey);

        return hash_equals($expectedSignature, $providedSignature);
    }
}
