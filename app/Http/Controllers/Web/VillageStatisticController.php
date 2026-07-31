<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\PopulationStatisticsRequest;
use App\Services\ImportedStatisticPageData;
use App\Services\StatisticPageData;
use App\Services\PopulationStatistics;
use App\Services\Statistics\PopulationStatisticAggregator;
use App\Services\Statistics\PopulationStatisticIndicatorService;
use App\Services\Web\PublicSiteService;
use App\Models\PopulationStatisticIndicator;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VillageStatisticController extends Controller
{
    public function index(PublicSiteService $site, PopulationStatistics $statistics): View
    {
        return $site->statistics($statistics);
    }

    public function show(
        Request $request,
        PublicSiteService $site,
        StatisticPageData $statisticPages,
        ImportedStatisticPageData $importedStatisticPages,
        string $section,
    ): View|RedirectResponse {
        if ($section === 'penduduk') {
            return redirect()->route('data-statistik.population');
        }

        if ($category = $site->findPublishedStatisticCategory($section)) {
            return $site->genericStatistic($importedStatisticPages->build(
                $category,
                $request->only(['dataset', 'from_year', 'to_year', 'sort']),
            ));
        }

        $context = $statisticPages->sectionContext($section, $request->query('menu'));
        if ($context) {
            return $site->genericStatistic($statisticPages->build(
                $context,
                $request->only(['from_year', 'to_year', 'sort']),
            ));
        }

        abort(404);
    }

    public function importedDataset(
        PublicSiteService $site,
        string $category,
        string $dataset,
    ): View {
        $category = $site->findPublishedStatisticCategory($category);
        abort_unless($category, 404);

        $dataset = $category->datasets->firstWhere('slug', $dataset);
        abort_unless($dataset, 404);

        return $site->importedStatisticDataset($category, $dataset);
    }

    public function population(
        PopulationStatisticsRequest $request,
        PublicSiteService $site,
        PopulationStatisticIndicatorService $indicatorService,
        PopulationStatisticAggregator $aggregator,
    ): View {
        $filters = $request->validated();
        $menu = $filters['menu'] ?? null;

        if ($menu) {
            $legacyMap = ['rentang-umur' => 'age_range'];
            $filters['indicator'] = $legacyMap[$menu] ?? null;
        }

        $indicators = $indicatorService->availableIndicators(true);
        $selectedKey = (string) ($filters['indicator'] ?? $indicators->first()?->key ?? '');
        $indicator = $indicators->firstWhere('key', $selectedKey) ?? $indicators->first();
        $result = $indicator ? $aggregator->aggregate($indicator) : null;
        if (! $indicator) {
            $indicator = PopulationStatisticIndicator::query()->where('key', 'gender')->first();
            $result = $indicator ? [
                'indicator' => ['key' => 'gender', 'label' => $indicator->label, 'unit' => $indicator->unit, 'chart_type' => $indicator->chart_type],
                'total' => 0,
                'classified' => 0,
                'items' => [
                    ['key' => 'L', 'label' => 'Laki-laki', 'value' => 0, 'percentage' => 0.0],
                    ['key' => 'P', 'label' => 'Perempuan', 'value' => 0, 'percentage' => 0.0],
                ],
            ] : null;
        }

        return $site->populationStatistics($indicators, $indicator, $result);
    }

    public function budgetHistory(PublicSiteService $site): View
    {
        return $site->budgetHistory();
    }
}
