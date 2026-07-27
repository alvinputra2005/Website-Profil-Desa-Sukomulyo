<?php

namespace Tests\Feature;

use App\Models\Official;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernmentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_government_page_renders_active_officials_in_their_visual_groups(): void
    {
        $leader = Official::create([
            'name' => 'Kepala Utama',
            'position' => 'Kepala Desa',
            'display_order' => 1,
            'is_active' => true,
        ]);
        $secretary = Official::create([
            'name' => 'Sekretaris Aktif',
            'position' => 'Sekretaris Desa',
            'superior_id' => $leader->id,
            'display_order' => 2,
            'is_active' => true,
        ]);
        Official::create([
            'name' => 'Kasi Aktif',
            'position' => 'Kasi Pemerintahan',
            'superior_id' => $leader->id,
            'display_order' => 3,
            'is_active' => true,
        ]);
        Official::create([
            'name' => 'Kaur Aktif',
            'position' => 'Kaur Keuangan',
            'superior_id' => $secretary->id,
            'display_order' => 4,
            'is_active' => true,
        ]);
        Official::create([
            'name' => 'Kasun Aktif',
            'position' => 'Kasun Bakir',
            'superior_id' => $leader->id,
            'display_order' => 5,
            'is_active' => true,
        ]);
        Official::create([
            'name' => 'Perangkat Nonaktif',
            'position' => 'Kasi Pelayanan',
            'superior_id' => $leader->id,
            'display_order' => 6,
            'is_active' => false,
        ]);

        $response = $this->get(route('pemerintahan-desa'))
            ->assertOk()
            ->assertSee('data-org-tree', false)
            ->assertSee('data-org-chart', false)
            ->assertSee('Pelaksana Teknis')
            ->assertSee('Sekretariat Desa')
            ->assertSee('Kepala Dusun')
            ->assertSee('Kepala Utama')
            ->assertSee('Sekretaris Aktif')
            ->assertSee('Kasi Aktif')
            ->assertSee('Kaur Aktif')
            ->assertSee('Kasun Aktif')
            ->assertDontSee('Perangkat Nonaktif')
            ->assertSee('org-node__fallback', false)
            ->assertSee('aria-pressed="false"', false);

        $this->assertSame(5, substr_count($response->getContent(), 'data-org-node'));
    }

    public function test_missing_known_photos_use_initial_placeholders(): void
    {
        Official::create([
            'name' => 'Baktiyar Kufain',
            'position' => 'Sekretaris Desa',
            'display_order' => 1,
            'is_active' => true,
        ]);
        Official::create([
            'name' => 'Cahyo Utomo',
            'position' => 'Kasun Talasan',
            'display_order' => 2,
            'is_active' => true,
        ]);

        $response = $this->get(route('pemerintahan-desa'))
            ->assertOk()
            ->assertSee('Baktiyar Kufain')
            ->assertSee('Cahyo Utomo')
            ->assertSee('BK')
            ->assertSee('CU')
            ->assertDontSee('baktiyar-kufain.jpeg', false)
            ->assertDontSee('cahyo-utomo.jpeg', false);

        $this->assertSame(0, substr_count($response->getContent(), 'data-org-image'));
    }
}
