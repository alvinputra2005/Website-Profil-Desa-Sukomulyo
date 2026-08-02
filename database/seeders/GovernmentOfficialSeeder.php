<?php

namespace Database\Seeders;

use App\Models\Official;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernmentOfficialSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $leader = $this->storeOfficial([
                'name' => 'Safiul Anwar',
                'title_suffix' => 'ST',
                'position' => 'Kepala Desa',
                'display_order' => 1,
                'organization_level' => 1,
                'organization_layout' => 'horizontal',
            ]);

            foreach ([
                ['name' => 'Angga Saputra', 'position' => 'Kasi Pemerintahan', 'display_order' => 2],
                ['name' => 'Wike Priharti Y', 'position' => 'Kasi Pelayanan', 'display_order' => 3],
                ['name' => 'Mohamad Sholeh', 'position' => 'Kasi Kesejahteraan', 'display_order' => 4],
            ] as $official) {
                $this->storeOfficial($official + [
                    'superior_id' => $leader->id,
                    'organization_level' => 2,
                ]);
            }

            $secretary = $this->storeOfficial([
                'name' => 'Baktiyar Kufain',
                'position' => 'Sekretaris Desa',
                'superior_id' => $leader->id,
                'display_order' => 5,
                'organization_level' => 2,
                'organization_layout' => 'horizontal',
            ]);

            foreach ([
                ['name' => 'Suwarno', 'position' => 'Kaur Keuangan', 'display_order' => 6],
                ['name' => 'Reza Tri Purnomo', 'position' => 'Kaur Perencanaan', 'display_order' => 7],
                ['name' => 'Catur Yulianto', 'position' => 'Kaur Tata Usaha dan Umum', 'display_order' => 8],
            ] as $official) {
                $this->storeOfficial($official + [
                    'superior_id' => $secretary->id,
                    'organization_level' => 3,
                ]);
            }

            foreach ([
                ['name' => 'Bambang S', 'position' => 'Kasun Bakir', 'display_order' => 9],
                ['name' => 'Sispanaji', 'position' => 'Kasun Biyan', 'display_order' => 10],
                ['name' => 'Nikita F Z', 'position' => 'Kasun Gumul', 'display_order' => 11],
                ['name' => 'Fendi Priyo S', 'position' => 'Kasun Kedungrejo', 'display_order' => 12],
                ['name' => 'Cahyo Utomo', 'position' => 'Kasun Talasan', 'display_order' => 13],
            ] as $official) {
                $this->storeOfficial($official + [
                    'superior_id' => $leader->id,
                    'organization_level' => 2,
                ]);
            }
        });
    }

    private function storeOfficial(array $attributes): Official
    {
        return Official::updateOrCreate(
            ['position' => $attributes['position']],
            $attributes + [
                'title_prefix' => null,
                'title_suffix' => null,
                'organization_offset' => 0,
                'organization_color' => '#526b42',
                'is_active' => true,
            ],
        );
    }
}
