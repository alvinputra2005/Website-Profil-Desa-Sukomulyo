<?php

namespace Tests\Unit;

use App\Services\BudgetHistoryData;
use Tests\TestCase;

class BudgetHistoryDataTest extends TestCase
{
    public function test_it_loads_public_history_from_json_with_2025_as_latest_year(): void
    {
        $history = (new BudgetHistoryData)->history();

        $this->assertCount(7, $history);
        $this->assertSame(2025, $history[0]['year']);
        $this->assertSame(2019, $history[6]['year']);
        $this->assertNotContains(2026, array_column($history, 'year'));
        $this->assertEquals(2461972089.84, $history[0]['income']);
    }

    public function test_it_loads_official_detail_without_simulated_programs_or_quarters(): void
    {
        $service = new BudgetHistoryData;
        $detail = $service->latest();

        $this->assertSame(2025, $detail['summary']['year']);
        $this->assertEquals(2090443879, $detail['summary']['realization']);
        $this->assertFalse($detail['programs_available']);
        $this->assertFalse($detail['quarters_available']);
        $this->assertSame([], $detail['programs']);
        $this->assertSame([], $detail['quarters']);
    }

    public function test_unavailable_revenue_values_remain_null(): void
    {
        $detail = (new BudgetHistoryData)->detail(2022);

        $this->assertNotNull($detail);
        $this->assertNull($detail['revenue'][0]['realization']);
        $this->assertNull($detail['revenue'][0]['percentage']);
    }

    public function test_years_outside_public_period_have_no_detail(): void
    {
        $service = new BudgetHistoryData;

        $this->assertNull($service->detail(2018));
        $this->assertNull($service->detail(2026));
    }
}
