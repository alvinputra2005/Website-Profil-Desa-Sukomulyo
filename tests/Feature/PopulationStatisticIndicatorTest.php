<?php

namespace Tests\Feature;

use App\Models\PopulationStatisticIndicator;
use App\Models\Resident;
use App\Services\Statistics\PopulationStatisticAggregator;
use App\Services\Statistics\PopulationStatisticCache;
use App\Services\Statistics\PopulationStatisticIndicatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopulationStatisticIndicatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_indicator_requires_an_existing_populated_source_and_enabled_state(): void
    {
        $service = app(PopulationStatisticIndicatorService::class);

        $this->assertCount(0, $service->availableIndicators());

        $resident = $this->resident('3300000000000001', null);
        $this->assertSame(['gender'], $service->availableIndicators()->pluck('key')->all());

        $resident->update(['birth_date' => '2000-01-01']);
        $this->assertSame(['gender', 'age_range'], $service->availableIndicators()->pluck('key')->all());

        PopulationStatisticIndicator::query()->where('key', 'gender')->update(['is_enabled' => false]);
        PopulationStatisticCache::flush();
        $this->assertSame(['age_range'], $service->availableIndicators()->pluck('key')->all());

        PopulationStatisticIndicator::query()->create([
            'key' => 'missing',
            'label' => 'Sumber Hilang',
            'slug' => 'sumber-hilang',
            'source_table' => 'residents',
            'source_column' => 'column_that_does_not_exist',
            'aggregation_type' => 'categorical',
            'is_enabled' => true,
            'is_public' => true,
        ]);
        PopulationStatisticCache::flush();
        $this->assertSame(['age_range'], $service->availableIndicators()->pluck('key')->all());
    }

    public function test_public_visibility_and_age_aggregation_are_registry_driven(): void
    {
        $this->resident('3300000000000001', '2024-01-01');
        $this->resident('3300000000000002', '2010-01-01');
        $age = PopulationStatisticIndicator::query()->where('key', 'age_range')->firstOrFail();
        $age->update(['is_public' => false]);

        $service = app(PopulationStatisticIndicatorService::class);
        $this->assertContains('age_range', $service->availableIndicators()->pluck('key'));
        $this->assertNotContains('age_range', $service->availableIndicators(true)->pluck('key'));

        $result = app(PopulationStatisticAggregator::class)->aggregate($age);
        $this->assertSame(2, $result['total']);
        $this->assertSame(1, collect($result['items'])->firstWhere('key', '0_4')['value']);
        $this->assertSame(1, collect($result['items'])->firstWhere('key', '15_24')['value']);
    }

    public function test_public_response_contains_aggregates_but_not_private_resident_fields(): void
    {
        $this->resident('3300000000000001', '2000-01-01');

        $this->get(route('data-statistik.population', ['indicator' => 'gender']))
            ->assertOk()
            ->assertSee('Jenis Kelamin')
            ->assertSee('1 jiwa')
            ->assertDontSee('3300000000000001')
            ->assertDontSee('Warga Rahasia')
            ->assertDontSee('Alamat Rahasia');
    }

    private function resident(string $nik, ?string $birthDate): Resident
    {
        return Resident::query()->create([
            'nik' => $nik,
            'name' => 'Warga Rahasia',
            'sex' => 'L',
            'birth_date' => $birthDate,
            'current_address' => 'Alamat Rahasia',
            'status' => 'active',
        ]);
    }
}
