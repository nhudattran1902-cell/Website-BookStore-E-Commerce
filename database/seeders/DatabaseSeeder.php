<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AllTableSeeder::class, // <-- Gọi AllTableSeeder tại đây
            MangaComicSeeder::class, // Nối seeder bộ truyện mới vào luồng seed chính
        ]);
    }
}
