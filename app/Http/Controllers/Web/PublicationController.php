<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\PublicSiteService;
use Illuminate\View\View;

class PublicationController extends Controller
{
    public function index(PublicSiteService $site): View
    {
        return $site->publicInformation();
    }

    public function show(PublicSiteService $site, string $section): View
    {
        return $site->informationDetail($section);
    }
}
