<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\PublicSiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class VillageMapController extends Controller
{
    public function index(PublicSiteService $site): View
    {
        return $site->map();
    }

    public function geoJson(PublicSiteService $site): JsonResponse
    {
        return $site->mapGeoJson();
    }
}
