<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\PopulationTrendRequest;
use App\Services\PopulationStatistics;
use App\Services\StatisticPageData;
use App\Services\Web\PublicSiteService;
use Illuminate\Http\Request;
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
        PopulationStatistics $statistics,
        StatisticPageData $statisticPages,
        string $section,
    ): View {
        if ($section === 'penduduk') {
            return $site->populationStatistics($statistics, $request->only(['from_year', 'to_year', 'sort']));
        }

        if ($category = $site->findPublishedStatisticCategory($section)) {
            return $site->importedStatisticCategory($category);
        }

        $context = $statisticPages->sectionContext($section, $request->query('menu'));
        abort_unless($context, 404);

        return $site->genericStatistic($statisticPages->build(
            $context,
            $request->only(['from_year', 'to_year', 'sort']),
        ));
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
        PopulationTrendRequest $request,
        PublicSiteService $site,
        PopulationStatistics $statistics,
        StatisticPageData $statisticPages,
    ): View {
        $filters = $request->validated();
        $menu = $filters['menu'] ?? null;

        if ($menu) {
            $context = $statisticPages->populationContext($menu);
            abort_unless($context, 404);

            return $site->genericStatistic($statisticPages->build($context, $filters));
        }

        return $site->populationStatistics($statistics, $filters);
    }

    public function budgetHistory(PublicSiteService $site): View
    {
        return $site->budgetHistory();
    }
}
