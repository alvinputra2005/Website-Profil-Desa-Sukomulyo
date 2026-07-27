<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\PublicSiteService;
use Symfony\Component\HttpFoundation\Response;

class SeoController extends Controller
{
    public function sitemap(PublicSiteService $site): Response
    {
        return $site->sitemap();
    }

    public function robots(PublicSiteService $site): Response
    {
        return $site->robots();
    }
}
