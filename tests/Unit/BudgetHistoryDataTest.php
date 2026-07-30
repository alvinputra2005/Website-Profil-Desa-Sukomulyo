<?php

namespace Tests\Unit;

use App\Services\BudgetHistoryData;
use Tests\TestCase;

class BudgetHistoryDataTest extends TestCase
{
    public function test_every_detail_breakdown_matches_its_annual_summary(): void
    {
        $service = new BudgetHistoryData;

        foreach ($service->history() as $summary) {
            $detail = $service->detail($summary['year']);

            $this->assertNotNull($detail);
            $this->assertSame($summary['income'], array_sum(array_column($detail['revenue'], 'budget')));
            $this->assertSame($summary['spending'], array_sum(array_column($detail['spending'], 'budget')));
            $this->assertSame($summary['realization'], array_sum(array_column($detail['spending'], 'realization')));
            $this->assertSame($summary['spending'], array_sum(array_column($detail['programs'], 'budget')));
            $this->assertSame($summary['realization'], array_sum(array_column($detail['programs'], 'realization')));
            $this->assertSame($summary['realization'], $detail['quarters'][3]['cumulative']);
            $this->assertSame(
                $detail['financing_summary']['receipt'] - $detail['financing_summary']['expenditure'],
                $detail['financing_summary']['net'],
            );
        }
    }

    public function test_unknown_year_has_no_detail(): void
    {
        $this->assertNull((new BudgetHistoryData)->detail(2016));
    }
}
