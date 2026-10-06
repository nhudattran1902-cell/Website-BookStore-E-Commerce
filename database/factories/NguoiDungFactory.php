<?php

namespace Database\Factories;

use App\Models\NguoiDung;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<NguoiDung>
 */
class NguoiDungFactory extends Factory
{
    protected $model = NguoiDung::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'ho_ten' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'mat_khau' => static::$password ??= Hash::make('password'),
            'so_dien_thoai' => fake()->phoneNumber(),
            'dia_chi_mac_dinh' => fake()->address(),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }
}
