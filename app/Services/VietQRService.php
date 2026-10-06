<?php

namespace App\Services;

use InvalidArgumentException;

class VietQRService
{
    public static function generateSecureQR(string $orderCode, int $amount): string
    {
        $secretKey = config('services.vietqr.signature_key');
        $bankId = config('services.vietqr.bank_id');
        $accountNumber = config('services.vietqr.account_no');

        if (! $secretKey || ! $bankId || ! $accountNumber) {
            throw new InvalidArgumentException('VietQR chưa được cấu hình thông tin tài khoản và khóa ký.');
        }

        $signature = self::signatureForOrder($orderCode, $amount);
        $parameters = [
            'amount' => $amount,
            'addInfo' => $orderCode.' SIG:'.$signature,
            'accountName' => config('services.vietqr.account_name'),
        ];

        return 'https://img.vietqr.io/image/'.rawurlencode($bankId).'-'.rawurlencode($accountNumber)
            .'-compact2.png?'.http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }

    public static function verifySignature(string $orderCode, int $amount, string $signature): bool
    {
        $secretKey = config('services.vietqr.signature_key');

        if (! is_string($secretKey) || $secretKey === '') {
            return false;
        }

        $expectedSignature = self::signatureForOrder($orderCode, $amount);

        return hash_equals($expectedSignature, $signature);
    }

    public static function signatureForOrder(string $orderCode, int $amount): string
    {
        $secretKey = config('services.vietqr.signature_key');

        if (! is_string($secretKey) || $secretKey === '') {
            throw new InvalidArgumentException('VietQR chưa được cấu hình khóa ký.');
        }

        return hash_hmac('sha256', "Order:{$orderCode}|Amount:{$amount}", $secretKey);
    }
}
