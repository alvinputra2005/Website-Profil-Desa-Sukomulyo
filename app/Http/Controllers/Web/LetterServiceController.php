<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LetterService;
use App\Services\Letters\LetterSettings;
use App\Services\Web\PublicSiteService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LetterServiceController extends Controller
{
    public function index(PublicSiteService $site, LetterSettings $settings): View
    {
        abort_unless($settings->enabled(), Response::HTTP_NOT_FOUND);
        $site->shareLayout();

        return view('pages.letters.index', ['services' => LetterService::query()->where('is_active', true)->orderBy('display_order')->get()]);
    }

    public function show(LetterService $letterService, PublicSiteService $site, LetterSettings $settings): View
    {
        abort_unless($settings->enabled() && $letterService->is_active, Response::HTTP_NOT_FOUND);
        $site->shareLayout();

        return view('pages.letters.show', compact('letterService', 'settings'));
    }
}
