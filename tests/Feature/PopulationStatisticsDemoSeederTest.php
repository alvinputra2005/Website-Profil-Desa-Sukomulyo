<?php

namespace Tests\Feature;

use App\Models\PopulationYearlySnapshot;
use App\Models\Resident;
use App\Services\PopulationStatistics;
use Database\Seeders\PopulationStatisticsDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopulationStatisticsDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_populates_consistent_visualization_data_and_is_idempotent(): void
    {
        $this->seed(PopulationStatisticsDemoSeeder::class);
        $this->seed(PopulationStatisticsDemoSeeder::class);

        $this->assertSame(60, Resident::query()->where('status', 'active')->count());
        $this->assertSame(31, Resident::query()->where('status', 'active')->where('sex', 'L')->count());
        $this->assertSame(29, Resident::query()->where('status', 'active')->where('sex', 'P')->count());
        $this->assertSame(7, PopulationYearlySnapshot::query()->count());
        $this->assertSame(7, PopulationYearlySnapshot::query()->where('is_published', true)->count());
        $this->assertDatabaseHas('population_yearly_snapshots', [
            'year' => 2026,
            'male_count' => 31,
            'female_count' => 29,
            'source' => PopulationStatistics::DEMO_SOURCE,
        ]);

        $summary = app(PopulationStatistics::class)->genderSummary();
        $this->assertSame(PopulationStatistics::DEMO_SOURCE, $summary['source']);
        $this->assertSame(60, $summary['total']);
    }
}
