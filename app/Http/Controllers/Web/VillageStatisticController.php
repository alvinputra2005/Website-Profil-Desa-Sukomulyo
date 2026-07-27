<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\PopulationPeriodRequest;
use App\Services\PopulationStatistics;
use App\Services\Web\PublicSiteService;
use Illuminate\View\View;

class VillageStatisticController extends Controller
{
    public function index(PublicSiteService $site, PopulationStatistics $statistics): View
    {
        return $site->statistics($statistics);
    }

    public function show(PublicSiteService $site, PopulationStatistics $statistics, string $section): View
    {
        return $site->statisticDetail($section, $statistics);
    }

    public function populationReport(
        PopulationPeriodRequest $request,
        PublicSiteService $site,
        PopulationStatistics $statistics,
    ): View {
        return $site->populationReport($request, $statistics);
    }

    public function budgetHistory(PublicSiteService $site): View
    {
        return $site->budgetHistory();
    }
}
