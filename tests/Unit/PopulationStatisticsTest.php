<?php

namespace Tests\Unit;

use App\Models\PopulationYearlySnapshot;
use App\Models\Resident;
use App\Services\PopulationStatistics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
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

    public function test_yearly_trend_is_chronological_and_calculates_change_and_growth(): void
    {
        $this->snapshot(2023, 55, 55);
        $this->snapshot(2022, 50, 50);

        $trend = app(PopulationStatistics::class)->yearlyTrend(2022, 2023);

        $this->assertSame([2022, 2023], array_column($trend, 'year'));
        $this->assertNull($trend[0]['change']);
        $this->assertNull($trend[0]['growth_percentage']);
        $this->assertSame(10, $trend[1]['change']);
        $this->assertSame(10.0, $trend[1]['growth_percentage']);
    }

    public function test_yearly_trend_keeps_gaps_excludes_drafts_and_handles_previous_zero(): void
    {
        $this->snapshot(2020, 0, 0);
        $this->snapshot(2021, 40, 60, false, 'Draf rahasia');
        $this->snapshot(2022, 45, 55);

        $trend = app(PopulationStatistics::class)->yearlyTrend(2020, 2022);

        $this->assertSame([2020, 2022], array_column($trend, 'year'));
        $this->assertNull($trend[1]['growth_percentage']);
        $this->assertSame(100, $trend[1]['change']);
        $this->assertNotContains('Draf rahasia', array_column($trend, 'source'));
    }

    public function test_yearly_trend_uses_current_active_data_when_current_snapshot_is_not_published(): void
    {
        $this->resident('3300000000000001', 'L');
        $this->resident('3300000000000002', 'P');
        $this->snapshot(2026, 999, 999, false, 'Snapshot belum publik');

        $trend = app(PopulationStatistics::class)->yearlyTrend(2026, 2026);

        $this->assertCount(1, $trend);
        $this->assertSame(1, $trend[0]['male']);
        $this->assertSame(1, $trend[0]['female']);
        $this->assertStringStartsWith('Data aktif per ', $trend[0]['source']);
    }

    public function test_yearly_trend_rejects_an_invalid_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(PopulationStatistics::class)->yearlyTrend(2026, 2025);
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

    private function snapshot(
        int $year,
        int $male,
        int $female,
        bool $published = true,
        ?string $source = 'Administrasi Desa',
    ): PopulationYearlySnapshot {
        return PopulationYearlySnapshot::create([
            'year' => $year,
            'male_count' => $male,
            'female_count' => $female,
            'reference_date' => "{$year}-12-31",
            'source' => $source,
            'is_published' => $published,
        ]);
    }
}
