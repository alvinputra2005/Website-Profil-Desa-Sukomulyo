<?php

namespace Database\Seeders;

use App\Models\PopulationYearlySnapshot;
use App\Models\Resident;
use App\Services\PopulationStatistics;
use App\Services\SiteCache;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PopulationStatisticsDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Seeder data dummy statistik hanya boleh dijalankan pada environment local atau testing.');
        }

        $snapshots = [
            2020 => ['male' => 25, 'female' => 23],
            2021 => ['male' => 26, 'female' => 24],
            2022 => ['male' => 27, 'female' => 25],
            2023 => ['male' => 28, 'female' => 26],
            2024 => ['male' => 29, 'female' => 27],
            2025 => ['male' => 30, 'female' => 28],
            2026 => ['male' => 31, 'female' => 29],
        ];

        foreach ($snapshots as $year => $counts) {
            $snapshot = PopulationYearlySnapshot::query()->where('year', $year)->first();

            if ($snapshot && $snapshot->source !== PopulationStatistics::DEMO_SOURCE) {
                continue;
            }

            PopulationYearlySnapshot::query()->updateOrCreate(
                ['year' => $year],
                [
                    'male_count' => $counts['male'],
                    'female_count' => $counts['female'],
                    'reference_date' => $year === 2026
                        ? now()->toDateString()
                        : Carbon::create($year, 12, 31)->toDateString(),
                    'source' => PopulationStatistics::DEMO_SOURCE,
                    'notes' => 'Data simulasi untuk memeriksa grafik, tabel, filter, dan tampilan responsif.',
                    'is_published' => true,
                ],
            );
        }

        $hasRealResidents = Resident::query()
            ->where(fn ($query) => $query
                ->whereNull('notes')
                ->orWhere('notes', '!=', PopulationStatistics::DEMO_MARKER))
            ->exists();

        if (! $hasRealResidents) {
            $residents = [];
            $now = now();

            foreach (range(1, 60) as $index) {
                $sex = $index <= 31 ? 'L' : 'P';
                $number = str_pad((string) $index, 4, '0', STR_PAD_LEFT);
                $residents[] = [
                    'nik' => '999900000000'.$number,
                    'name' => ($sex === 'L' ? 'Laki-laki' : 'Perempuan').' Dummy '.$number,
                    'sex' => $sex,
                    'birth_place' => 'Sukomulyo',
                    'birth_date' => Carbon::create(1975 + ($index % 35), ($index % 12) + 1, ($index % 27) + 1)->toDateString(),
                    'religion' => 'Islam',
                    'marital_status' => $index % 3 === 0 ? 'Belum Kawin' : 'Kawin',
                    'citizenship' => 'WNI',
                    'education' => ['SD/Sederajat', 'SLTP/Sederajat', 'SLTA/Sederajat', 'Diploma/Sarjana'][$index % 4],
                    'occupation' => ['Petani/Pekebun', 'Wiraswasta', 'Pelajar/Mahasiswa', 'Mengurus Rumah Tangga'][$index % 4],
                    'resident_status' => 'permanent',
                    'status' => 'active',
                    'registered_at' => now()->toDateString(),
                    'notes' => PopulationStatistics::DEMO_MARKER,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('residents')->upsert(
                $residents,
                ['nik'],
                [
                    'name',
                    'sex',
                    'birth_place',
                    'birth_date',
                    'religion',
                    'marital_status',
                    'citizenship',
                    'education',
                    'occupation',
                    'resident_status',
                    'status',
                    'registered_at',
                    'notes',
                    'updated_at',
                ],
            );
        }

        app(SiteCache::class)->invalidatePopulationStatistics();
    }
}
