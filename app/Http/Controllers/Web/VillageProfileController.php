<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\VillageComment;
use App\Services\Web\PublicSiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VillageProfileController extends Controller
{
    public function index(PublicSiteService $site): View
    {
        return $site->profile();
    }

    public function storeComment(Request $request, PublicSiteService $site): RedirectResponse
    {
        return $site->sendProfileComment($request);
    }

    public function comments(PublicSiteService $site): View
    {
        return $site->profileComments();
    }

    public function sectionComments(PublicSiteService $site, string $section): View
    {
        return $site->profileSectionComments($section);
    }

    public function likeComment(PublicSiteService $site, VillageComment $comment): RedirectResponse
    {
        return $site->likeProfileComment($comment);
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
