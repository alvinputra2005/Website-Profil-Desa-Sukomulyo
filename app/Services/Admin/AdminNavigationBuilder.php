<?php

namespace App\Services\Admin;

use App\Models\StatisticCategory;
use App\Services\Statistics\PopulationStatisticIndicatorService;
use Illuminate\Support\Facades\Schema;

final class AdminNavigationBuilder
{
    public function __construct(
        private readonly PopulationStatisticIndicatorService $populationIndicators,
    ) {}

    public function statisticItems(): array
    {
        $items = [];

        if ($this->populationIndicators->availableIndicators()->isNotEmpty()) {
            $items[] = ['Penduduk Terkini', 'admin.statistics.population.show', null];
        }

        if (Schema::hasTable('statistic_categories') && Schema::hasTable('statistic_datasets') && Schema::hasTable('statistic_rows')) {
            $categories = StatisticCategory::query()
                ->where('is_active', true)
                ->whereHas('datasets', fn ($query) => $query->whereHas('rows'))
                ->orderBy('display_order')
                ->orderBy('name')
                ->get();

            foreach ($categories as $category) {
                $items[] = [$category->name, 'admin.statistics.categories.show', $category->slug];
            }
        }

        $items[] = ['Import Statistik', 'admin.statistics.import.create', null];

        return $items;
    }
}
