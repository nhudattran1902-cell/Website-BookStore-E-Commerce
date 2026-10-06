<?php

namespace App\Services;

class TotpService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(): string
    {
        return self::base32Encode(random_bytes(20));
    }

    public static function provisioningUri(string $secret, string $account): string
    {
        $issuer = 'BOOK & BOX';
        $label = rawurlencode($issuer.':'.$account);
        $query = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ], '', '&', PHP_QUERY_RFC3986);

        return "otpauth://totp/{$label}?{$query}";
    }

    public static function verify(string $secret, string $code, ?int $timestamp = null): ?int
    {
        if (! preg_match('/^\d{6}$/', $code)) {
            return null;
        }

        $key = self::base32Decode($secret);
        $currentCounter = intdiv($timestamp ?? time(), 30);

        for ($offset = -1; $offset <= 1; $offset++) {
            $counter = $currentCounter + $offset;
            $high = intdiv($counter, 0x100000000);
            $low = $counter % 0x100000000;
            $hash = hash_hmac('sha1', pack('N2', $high, $low), $key, true);
            $offsetByte = ord($hash[19]) & 0x0F;
            $binary = unpack('N', substr($hash, $offsetByte, 4))[1] & 0x7FFFFFFF;
            $expected = str_pad((string) ($binary % 1000000), 6, '0', STR_PAD_LEFT);

            if (hash_equals($expected, $code)) {
                return $counter;
            }
        }

        return null;
    }

    private static function base32Encode(string $value): string
    {
        $encoded = '';
        $buffer = 0;
        $bits = 0;

        foreach (unpack('C*', $value) as $byte) {
            $buffer = ($buffer << 8) | $byte;
            $bits += 8;

            while ($bits >= 5) {
                $bits -= 5;
                $encoded .= self::BASE32_ALPHABET[($buffer >> $bits) & 31];
            }

            $buffer &= (1 << $bits) - 1;
        }

        if ($bits > 0) {
            $encoded .= self::BASE32_ALPHABET[($buffer << (5 - $bits)) & 31];
        }

        return $encoded;
    }

    private static function base32Decode(string $value): string
    {
        $decoded = '';
        $buffer = 0;
        $bits = 0;

        foreach (str_split(strtoupper(rtrim($value, '='))) as $character) {
            $digit = strpos(self::BASE32_ALPHABET, $character);

            if ($digit === false) {
                return '';
            }

            $buffer = ($buffer << 5) | $digit;
            $bits += 5;

            if ($bits >= 8) {
                $bits -= 8;
                $decoded .= chr(($buffer >> $bits) & 0xFF);
            }

            $buffer &= (1 << $bits) - 1;
        }

        return $decoded;
    }
}
