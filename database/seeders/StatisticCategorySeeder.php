<?php

namespace Database\Seeders;

use App\Models\StatisticCategory;
use Illuminate\Database\Seeder;

class StatisticCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kependudukan', 'slug' => 'kependudukan', 'icon' => 'fa-users'],
            ['name' => 'Pendidikan', 'slug' => 'pendidikan', 'icon' => 'fa-graduation-cap'],
            ['name' => 'Pekerjaan', 'slug' => 'pekerjaan', 'icon' => 'fa-briefcase'],
            ['name' => 'Kesehatan', 'slug' => 'kesehatan', 'icon' => 'fa-heartbeat'],
            ['name' => 'Keluarga', 'slug' => 'keluarga', 'icon' => 'fa-home'],
            ['name' => 'Keluarga Berencana', 'slug' => 'keluarga-berencana', 'icon' => 'fa-venus-mars'],
            ['name' => 'Perlindungan Sosial', 'slug' => 'perlindungan-sosial', 'icon' => 'fa-shield'],
            ['name' => 'Lainnya', 'slug' => 'lainnya', 'icon' => 'fa-table'],
        ];

        foreach ($categories as $order => $category) {
            StatisticCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category + [
                    'display_order' => $order + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
