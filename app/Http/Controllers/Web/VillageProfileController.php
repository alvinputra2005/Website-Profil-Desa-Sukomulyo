<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\PublicSiteService;
use Illuminate\View\View;

class VillageProfileController extends Controller
{
    public function index(PublicSiteService $site): View
    {
        return $site->profile();
    }

    public function show(PublicSiteService $site, string $section): View
    {
        return $site->profileDetail($section);
    }

    public function government(PublicSiteService $site): View
    {
        return $site->government();
    }

    public function potentials(PublicSiteService $site): View
    {
        return $site->potentials();
    }
}
