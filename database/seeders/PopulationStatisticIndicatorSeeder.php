<?php

namespace Database\Seeders;

use App\Models\PopulationStatisticIndicator;
use Illuminate\Database\Seeder;

class PopulationStatisticIndicatorSeeder extends Seeder
{
    public function run(): void
    {
        PopulationStatisticIndicator::query()->updateOrCreate(
            ['key' => 'gender'],
            [
                'label' => 'Jenis Kelamin',
                'slug' => 'jenis-kelamin',
                'source_table' => 'residents',
                'source_column' => 'sex',
                'aggregation_type' => 'categorical',
                'configuration_json' => [
                    'labels' => ['L' => 'Laki-laki', 'P' => 'Perempuan'],
                    'include_unknown' => true,
                ],
                'unit' => 'jiwa',
                'chart_type' => 'doughnut',
                'icon' => 'fa-venus-mars',
                'is_enabled' => true,
                'is_public' => true,
                'display_order' => 1,
            ],
        );

        PopulationStatisticIndicator::query()->updateOrCreate(
            ['key' => 'age_range'],
            [
                'label' => 'Rentang Umur',
                'slug' => 'rentang-umur',
                'source_table' => 'residents',
                'source_column' => 'birth_date',
                'aggregation_type' => 'age_range',
                'configuration_json' => [
                    'source_mode' => 'birth_date',
                    'ranges' => [
                        ['key' => '0_4', 'label' => '0–4 Tahun', 'min' => 0, 'max' => 4],
                        ['key' => '5_14', 'label' => '5–14 Tahun', 'min' => 5, 'max' => 14],
                        ['key' => '15_24', 'label' => '15–24 Tahun', 'min' => 15, 'max' => 24],
                        ['key' => '25_44', 'label' => '25–44 Tahun', 'min' => 25, 'max' => 44],
                        ['key' => '45_64', 'label' => '45–64 Tahun', 'min' => 45, 'max' => 64],
                        ['key' => '65_plus', 'label' => '65+ Tahun', 'min' => 65, 'max' => null],
                    ],
                ],
                'unit' => 'jiwa',
                'chart_type' => 'bar',
                'icon' => 'fa-bar-chart',
                'is_enabled' => true,
                'is_public' => true,
                'display_order' => 2,
            ],
        );
    }
}
