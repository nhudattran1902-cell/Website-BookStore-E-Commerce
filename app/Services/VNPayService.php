<?php

namespace App\Services;

use InvalidArgumentException;

class VNPayService
{
    public static function createPaymentUrl(string $orderCode, float $amount, ?string $ipAddress = null): string
    {
        $tmnCode = config('services.vnpay.tmn_code');
        $secret = config('services.vnpay.hash_secret');
        $baseUrl = config('services.vnpay.base_url');

        if (! $tmnCode || ! $secret || ! $baseUrl) {
            throw new InvalidArgumentException('VNPay chưa được cấu hình merchant credentials.');
        }

        $data = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $tmnCode,
            'vnp_Amount' => (int) round($amount * 100),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $orderCode,
            'vnp_OrderInfo' => 'Thanh toan don hang '.$orderCode,
            'vnp_OrderType' => 'billpayment',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => route('checkout.success', $orderCode),
            'vnp_IpAddr' => $ipAddress ?? request()->ip() ?? '127.0.0.1',
            'vnp_CreateDate' => now()->format('YmdHis'),
        ];
        ksort($data);

        $query = self::canonicalQuery($data);
        $signature = hash_hmac('sha512', $query, $secret);

        return $baseUrl.'?'.$query.'&vnp_SecureHash='.$signature;
    }

    public static function verifyResponse(array $input): bool
    {
        $secret = config('services.vnpay.hash_secret');
        $providedSignature = $input['vnp_SecureHash'] ?? null;

        if (! is_string($secret) || $secret === '' || ! is_string($providedSignature)) {
            return false;
        }

        $data = [];
        foreach ($input as $key => $value) {
            if (str_starts_with((string) $key, 'vnp_')
                && ! in_array($key, ['vnp_SecureHash', 'vnp_SecureHashType'], true)
                && is_scalar($value)) {
                $data[$key] = (string) $value;
            }
        }
        ksort($data);

        $query = self::canonicalQuery($data);
        $expectedSignature = hash_hmac('sha512', $query, $secret);

        return hash_equals($expectedSignature, $providedSignature);
    }

    private static function canonicalQuery(array $data): string
    {
        $pairs = [];

        foreach ($data as $key => $value) {
            $pairs[] = urlencode((string) $key).'='.urlencode((string) $value);
        }

        return implode('&', $pairs);
    }
}
