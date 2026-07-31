<?php

namespace Tests\Unit;

use App\Models\Resident;
use App\Services\PopulationStatistics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopulationStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('village.population_year', 2026);
    }

    public function test_gender_summary_counts_active_residents_and_calculates_percentages(): void
    {
        $this->resident('3300000000000001', 'L');
        $this->resident('3300000000000002', 'L');
        $this->resident('3300000000000003', 'P');
        $this->resident('3300000000000004', 'P', 'deceased');

        $summary = app(PopulationStatistics::class)->genderSummary();

        $this->assertSame(2026, $summary['year']);
        $this->assertSame(2, $summary['male']);
        $this->assertSame(1, $summary['female']);
        $this->assertSame(3, $summary['total']);
        $this->assertSame(66.67, $summary['male_percentage']);
        $this->assertSame(33.33, $summary['female_percentage']);
        $this->assertNotNull($summary['updated_at']);
    }

    public function test_gender_summary_does_not_divide_by_zero(): void
    {
        $summary = app(PopulationStatistics::class)->genderSummary();

        $this->assertSame(0, $summary['total']);
        $this->assertSame(0.0, $summary['male_percentage']);
        $this->assertSame(0.0, $summary['female_percentage']);
        $this->assertNull($summary['updated_at']);
    }

    private function resident(string $nik, string $sex, string $status = 'active'): Resident
    {
        return Resident::create([
            'nik' => $nik,
            'name' => 'Penduduk '.$nik,
            'sex' => $sex,
            'status' => $status,
        ]);
    }
}
