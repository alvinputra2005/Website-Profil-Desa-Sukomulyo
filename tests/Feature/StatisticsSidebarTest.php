<?php

namespace Tests\Feature;

use App\Models\Resident;
use App\Services\SiteCache;
use App\Services\Statistics\PopulationStatisticCache;
use Database\Seeders\PopulationStatisticIndicatorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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
            ->assertDontSee('Semua Dataset')
            ->assertDontSee('Lihat Semua')
            ->assertDontSee('id="population-indicator"', false)
            ->assertDontSee('class="population-year-filter"', false)
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

    public function test_statistics_landing_page_renders_normalized_census_gender_distribution(): void
    {
        Storage::fake('public');
        Resident::query()->create([
            'nik' => '3300000000000002',
            'name' => 'Warga Sensus',
            'sex' => 'L',
            'birth_date' => '2000-01-01',
            'status' => 'active',
        ]);
        Storage::disk('public')->put('statistics/sensus_normalized.json', json_encode([
            'datasets' => [
                [
                    'dataset_id' => 'ik_tabel_4_2021',
                    'rows' => [
                        ['jumlah_individu_laki_laki_dalam_keluarga' => 12],
                    ],
                ],
                [
                    'dataset_id' => 'ik_tabel_5_2021',
                    'rows' => [
                        ['jumlah_individu_perempuan_dalam_keluarga' => 13],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR));
        $this->seed(PopulationStatisticIndicatorSeeder::class);
        Cache::forget(PopulationStatisticCache::PUBLIC_INDICATORS);
        Cache::forget(SiteCache::PUBLIC_STATISTICS);

        $this->get(route('data-desa-statistik'))
            ->assertOk()
            ->assertSee('Jenis Kelamin')
            ->assertSee('12 jiwa')
            ->assertSee('13 jiwa');
    }
}
