<?php

namespace Database\Seeders;

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
