<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\PublicSiteService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends Controller
{
    public function index(Request $request, PublicSiteService $site): View
    {
        return $site->news($request);
    }

    public function show(PublicSiteService $site, string $slug): View|Response
    {
        return $site->article($slug);
    }

    public function category(Request $request, PublicSiteService $site, string $category): View|Response
    {
        return $site->category($request, $category);
    }

    public function archive(Request $request, PublicSiteService $site, ?string $year = null): View
    {
        return $site->archive($request, $year);
    }

    public function search(Request $request, PublicSiteService $site): View
    {
        return $site->search($request);
    }
}
