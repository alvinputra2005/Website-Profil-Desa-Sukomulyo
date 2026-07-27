<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\PublicSiteService;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends Controller
{
    public function __invoke(PublicSiteService $site): Response
    {
        return $site->notFound();
    }
}
