<?php

namespace Tests\Feature;

use App\Models\Official;
use App\Queries\OfficialOrganizationQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialOrganizationQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_active_officials_from_their_superior_relationship(): void
    {
        $leader = Official::create([
            'name' => 'Kepala Desa',
            'position' => 'Kepala Desa',
            'display_order' => 1,
            'is_active' => true,
        ]);
        $secretary = Official::create([
            'name' => 'Sekretaris Desa',
            'position' => 'Sekretaris Desa',
            'superior_id' => $leader->id,
            'display_order' => 2,
            'is_active' => true,
        ]);
        Official::create([
            'name' => 'Kaur Keuangan',
            'position' => 'Kaur Keuangan',
            'superior_id' => $secretary->id,
            'display_order' => 3,
            'is_active' => true,
        ]);
        Official::create([
            'name' => 'Perangkat Nonaktif',
            'position' => 'Kasi Pelayanan',
            'display_order' => 4,
            'is_active' => false,
        ]);

        $nodes = app(OfficialOrganizationQuery::class)->tree();

        $this->assertCount(1, $nodes);
        $this->assertSame($leader->id, $nodes[0]['official']->id);
        $this->assertSame($secretary->id, $nodes[0]['children'][0]['official']->id);
        $this->assertSame('Kaur Keuangan', $nodes[0]['children'][0]['children'][0]['official']->name);
    }
}
