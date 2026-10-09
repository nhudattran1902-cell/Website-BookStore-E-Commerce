<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Uses the post-2025, two-level Vietnamese administrative division dataset.
 * Dataset attribution: Open Admin Data, CC BY 4.0, retrieved 2026-10-06.
 *
 * @see https://github.com/open-admin-data/vietnam-administrative-divisions
 * @see https://xaydungchinhsach.chinhphu.vn/bang-danh-muc-va-ma-so-cua-34-tinh-thanh-moi-cac-don-vi-hanh-chinh-cap-xa-moi-11925070418263625.htm
 */
class AddressLocationService
{
    /**
     * @return array<string, string>
     */
    public function provinces(): array
    {
        $provinces = [];

        foreach ($this->units() as $unit) {
            $provinces[$unit['parent']['id']] = $unit['parent']['name']['local'];
        }

        asort($provinces, SORT_NATURAL | SORT_FLAG_CASE);

        return $provinces;
    }

    /**
     * @return array<string, string>
     */
    public function wardsForProvince(string $provinceCode): array
    {
        $wards = [];

        foreach ($this->units() as $unit) {
            if ($unit['parent']['id'] === $provinceCode) {
                $wards[$unit['id']] = $unit['name']['local'];
            }
        }

        asort($wards, SORT_NATURAL | SORT_FLAG_CASE);

        return $wards;
    }

    public function formatAddress(string $houseAndStreet, string $provinceCode, string $wardCode): string
    {
        $provinces = $this->provinces();
        $wards = $this->wardsForProvince($provinceCode);

        if (! array_key_exists($provinceCode, $provinces)) {
            throw ValidationException::withMessages([
                'province_code' => 'Vui lòng chọn tỉnh/thành phố trong danh sách.',
            ]);
        }

        if (! array_key_exists($wardCode, $wards)) {
            throw ValidationException::withMessages([
                'ward_code' => 'Phường/xã không thuộc tỉnh/thành phố đã chọn.',
            ]);
        }

        return trim($houseAndStreet).', '.$wards[$wardCode].', '.$provinces[$provinceCode];
    }

    /**
     * @return list<array{id: string, name: array{local: string}, parent: array{id: string, name: array{local: string}}}>
     */
    private function units(): array
    {
        return Cache::rememberForever('vietnam-admin-communes-v2025', function (): array {
            $path = resource_path('data/vietnam-wards.json');

            if (! is_file($path) || ! is_readable($path)) {
                throw new RuntimeException('Danh mục phường/xã Việt Nam không tồn tại hoặc không đọc được.');
            }

            $units = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($units)) {
                throw new RuntimeException('Danh mục phường/xã Việt Nam không hợp lệ.');
            }

            return $units;
        });
    }
}
