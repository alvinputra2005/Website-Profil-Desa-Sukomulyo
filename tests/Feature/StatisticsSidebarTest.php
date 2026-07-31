<?php

namespace Tests\Feature;

use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_statistics_subpages_show_available_datasets_in_an_accordion(): void
    {
        Resident::query()->create([
            'nik' => '3300000000000001',
            'name' => 'Warga Uji',
            'sex' => 'L',
            'birth_date' => '2000-01-01',
            'status' => 'active',
        ]);

        $this->get(route('data-statistik.population'))
            ->assertOk()
            ->assertSee('aria-label="Navigasi data statistik"', false)
            ->assertSee('data-no-scroll-reveal', false)
            ->assertSee('data-sidebar-accordion', false)
            ->assertSee('data-sidebar-accordion-toggle', false)
            ->assertSee('Semua Dataset')
            ->assertSee('Penduduk Terkini')
            ->assertSee('Rentang Umur')
            ->assertSee('Jenis Kelamin')
            ->assertDontSee('Agama')
            ->assertDontSee('Akta Kelahiran')
            ->assertDontSee('Golongan Darah')
            ->assertSee('Diperbarui 28 Juli 2026')
            ->assertSee('Pemerintah Desa Sukomulyo')
            ->assertSee('data-population-export-toggle="actual"', false)
            ->assertSee('Unduh Data Aktual');
    }

    public function test_statistics_landing_page_stays_full_width_without_the_sidebar(): void
    {
        $this->get(route('data-desa-statistik'))
            ->assertOk()
            ->assertDontSee('aria-label="Navigasi data statistik"', false)
            ->assertDontSee('data-sidebar-accordion', false);
    }
}
