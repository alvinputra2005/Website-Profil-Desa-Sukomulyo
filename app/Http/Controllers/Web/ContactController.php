<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreContactMessageRequest;
use App\Services\Web\PublicSiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(PublicSiteService $site): View
    {
        return $site->contact();
    }

    public function store(StoreContactMessageRequest $request, PublicSiteService $site): RedirectResponse
    {
        return $site->sendContact($request);
    }
}
