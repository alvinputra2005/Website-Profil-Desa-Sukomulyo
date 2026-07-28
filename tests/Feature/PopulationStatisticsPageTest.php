<?php

namespace Tests\Feature;

use App\Models\PopulationYearlySnapshot;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
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

    public function test_public_population_page_shows_live_gender_data_and_accessible_fallbacks(): void
    {
        $this->resident('3300000000000001', 'L');
        $this->resident('3300000000000002', 'L');
        $this->resident('3300000000000003', 'P');

        $this->get(route('data-statistik.population'))
            ->assertOk()
            ->assertSee('Statistik Penduduk')
            ->assertDontSee('Tahun Data 2026')
            ->assertSee('2 jiwa')
            ->assertSee('1 jiwa')
            ->assertSee('3 jiwa')
            ->assertSee('66,67%')
            ->assertSee('33,33%')
            ->assertSee('data-population-pie', false)
            ->assertSee('data-population-line', false)
            ->assertSee('role="img"', false)
            ->assertSee('population-summary-table', false)
            ->assertSee('population-history-table', false)
            ->assertSee('Data 2020 dapat menggunakan basis Sensus Penduduk 2020');
    }

    public function test_public_page_has_a_readable_empty_state_and_zero_percentages(): void
    {
        $this->get(route('data-statistik.population'))
            ->assertOk()
            ->assertSee('Data komposisi penduduk tahun 2026 belum tersedia.')
            ->assertSee('0 jiwa')
            ->assertSee('0,00%');
    }

    public function test_only_published_snapshots_are_exposed_to_the_public_page(): void
    {
        $this->snapshot(2024, true, 'Rekap publik 2024');
        $this->snapshot(2025, false, 'Sumber snapshot draf rahasia');

        $this->get(route('data-statistik.population'))
            ->assertOk()
            ->assertSee('Rekap publik 2024')
            ->assertDontSee('Sumber snapshot draf rahasia');
    }

    public function test_valid_year_filter_is_reflected_and_invalid_filters_are_rejected(): void
    {
        $this->snapshot(2020);
        $this->snapshot(2026);

        $this->get(route('data-statistik.population', [
            'from_year' => 2020,
            'to_year' => 2026,
            'sort' => 'desc',
        ]))
            ->assertOk()
            ->assertSee('"from":2020', false)
            ->assertSee('"to":2026', false)
            ->assertSee('"sort":"desc"', false);

        $this->from(route('data-statistik.population'))
            ->get(route('data-statistik.population', ['from_year' => 2026, 'to_year' => 2020]))
            ->assertRedirect(route('data-statistik.population'))
            ->assertSessionHasErrors('to_year');

        $this->from(route('data-statistik.population'))
            ->get(route('data-statistik.population', ['to_year' => 2027]))
            ->assertRedirect(route('data-statistik.population'))
            ->assertSessionHasErrors('to_year');
    }

    public function test_admin_can_manage_and_publish_yearly_snapshots(): void
    {
        $role = Role::create(['name' => 'Admin Data', 'code' => 'admin_data']);
        $admin = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

        $this->actingAs($admin)
            ->get(route('admin.population.yearly-snapshots.index'))
            ->assertOk()
            ->assertSee('Statistik Tahunan');

        $this->post(route('admin.population.yearly-snapshots.store'), [
            'year' => 2025,
            'male_count' => 1200,
            'female_count' => 1175,
            'reference_date' => '2025-12-31',
            'source' => 'Administrasi Desa',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $snapshot = PopulationYearlySnapshot::query()->where('year', 2025)->firstOrFail();
        $this->assertSame(2375, $snapshot->total_count);
        $this->assertFalse($snapshot->is_published);

        $this->patch(route('admin.population.yearly-snapshots.toggle-publication', $snapshot))
            ->assertRedirect();
        $this->assertTrue($snapshot->fresh()->is_published);

        $this->put(route('admin.population.yearly-snapshots.update', $snapshot), [
            'year' => 2025,
            'male_count' => 1210,
            'female_count' => 1180,
            'reference_date' => '2025-12-31',
            'source' => 'Administrasi Desa Terverifikasi',
            'is_published' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('population_yearly_snapshots', [
            'year' => 2025,
            'male_count' => 1210,
            'female_count' => 1180,
            'is_published' => true,
        ]);

        $this->delete(route('admin.population.yearly-snapshots.destroy', $snapshot))
            ->assertRedirect(route('admin.population.yearly-snapshots.index'));
        $this->assertDatabaseMissing('population_yearly_snapshots', ['id' => $snapshot->id]);
    }

    public function test_admin_snapshot_validation_rejects_duplicates_and_negative_counts(): void
    {
        $role = Role::create(['name' => 'Admin Data', 'code' => 'admin_data']);
        $admin = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $this->snapshot(2025);

        $this->actingAs($admin)
            ->from(route('admin.population.yearly-snapshots.create'))
            ->post(route('admin.population.yearly-snapshots.store'), [
                'year' => 2025,
                'male_count' => -1,
                'female_count' => 100,
            ])
            ->assertRedirect(route('admin.population.yearly-snapshots.create'))
            ->assertSessionHasErrors(['year', 'male_count']);
    }

    public function test_snapshot_changes_invalidate_public_population_caches(): void
    {
        Cache::put(SiteCache::PUBLIC_POPULATION_STATISTICS, ['stale' => true]);
        Cache::put(SiteCache::PUBLIC_POPULATION_TREND, ['stale' => true]);

        $this->snapshot(2025);

        $this->assertFalse(Cache::has(SiteCache::PUBLIC_POPULATION_STATISTICS));
        $this->assertFalse(Cache::has(SiteCache::PUBLIC_POPULATION_TREND));
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

    private function snapshot(
        int $year,
        bool $published = true,
        string $source = 'Administrasi Desa',
    ): PopulationYearlySnapshot {
        return PopulationYearlySnapshot::create([
            'year' => $year,
            'male_count' => 100,
            'female_count' => 100,
            'reference_date' => "{$year}-12-31",
            'source' => $source,
            'is_published' => $published,
        ]);
    }
}
