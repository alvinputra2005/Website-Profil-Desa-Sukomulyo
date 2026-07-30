<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AnnouncementIndexRequest;
use App\Models\Publication;
use App\Services\Announcements\AnnouncementQueryService;
use App\Services\Web\PublicSiteService;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(
        AnnouncementIndexRequest $request,
        AnnouncementQueryService $announcements,
        PublicSiteService $site,
    ): View {
        $filters = $request->filters();

        return $site->page('pages.announcements.index', [
            'announcements' => $announcements->paginate($filters),
            'filters' => $filters,
        ]);
    }

    public function show(
        Publication $publication,
        AnnouncementQueryService $announcements,
        PublicSiteService $site,
    ): View {
        $announcement = $announcements->findPublished($publication->slug);

        return $site->page('pages.announcements.show', compact('announcement'));
    }
}
