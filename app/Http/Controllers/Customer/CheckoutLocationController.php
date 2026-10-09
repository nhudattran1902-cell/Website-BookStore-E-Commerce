<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\AddressLocationService;
use Illuminate\Http\JsonResponse;

class CheckoutLocationController extends Controller
{
    public function provinces(AddressLocationService $locations): JsonResponse
    {
        $provinces = collect($locations->provinces())
            ->map(fn (string $name, string $id): array => ['id' => $id, 'name' => $name])
            ->values();

        return response()->json(['provinces' => $provinces]);
    }

    public function wards(string $provinceCode, AddressLocationService $locations): JsonResponse
    {
        $wards = $locations->wardsForProvince($provinceCode);

        abort_if($wards === [], 404);

        $wardOptions = collect($wards)
            ->map(fn (string $name, string $id): array => ['id' => $id, 'name' => $name])
            ->values();

        return response()->json(['wards' => $wardOptions]);
    }
}
