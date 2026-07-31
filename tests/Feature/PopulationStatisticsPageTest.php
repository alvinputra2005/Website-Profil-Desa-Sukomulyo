<?php

namespace Tests\Feature;

use App\Models\Resident;
use App\Services\SiteCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PopulationStatisticsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('village.population_year', 2026);
    }

    public function test_public_population_page_shows_live_gender_data_without_yearly_statistics(): void
    {
        $this->resident('3300000000000001', 'L');
        $this->resident('3300000000000002', 'L');
        $this->resident('3300000000000003', 'P');

        $this->get(route('data-statistik.population'))
            ->assertOk()
            ->assertSee('Statistik Penduduk')
            ->assertSee('2 jiwa')
            ->assertSee('1 jiwa')
            ->assertSee('3 jiwa')
            ->assertSee('66,67%')
            ->assertSee('33,33%')
            ->assertDontSee('Pertumbuhan Penduduk Tahunan')
            ->assertDontSee('Unduh Data Tahunan')
            ->assertSee('data-population-pie', false);
    }

    public function test_public_page_has_a_readable_empty_state_and_zero_percentages(): void
    {
        $this->get(route('data-statistik.population'))
            ->assertOk()
            ->assertSee('Data komposisi penduduk tahun 2026 belum tersedia.')
            ->assertSee('0 jiwa')
            ->assertSee('0,00%');
    }

    public function test_resident_changes_invalidate_public_population_cache(): void
    {
        Cache::put(SiteCache::PUBLIC_POPULATION_STATISTICS, ['stale' => true]);

        $this->resident('3300000000000001', 'L');

        $this->assertFalse(Cache::has(SiteCache::PUBLIC_POPULATION_STATISTICS));
    }

    private function resident(string $nik, string $sex): Resident
    {
        return Resident::create([
            'nik' => $nik,
            'name' => 'Penduduk '.$nik,
            'sex' => $sex,
            'status' => 'active',
        ]);
    }
}
