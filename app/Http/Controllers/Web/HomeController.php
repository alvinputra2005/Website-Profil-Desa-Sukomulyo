<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PopulationStatistics;
use App\Services\Web\PublicSiteService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(PublicSiteService $site, PopulationStatistics $statistics): View
    {
        return $site->home($statistics);
    }
}
