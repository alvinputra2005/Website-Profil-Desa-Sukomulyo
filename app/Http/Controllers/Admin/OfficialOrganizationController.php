<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Queries\OfficialOrganizationQuery;
use Illuminate\View\View;

final class OfficialOrganizationController extends Controller
{
    public function __invoke(OfficialOrganizationQuery $query): View
    {
        abort_unless(auth()->user()?->can('manage-content'), 403);

        return view('admin.officials.organization', [
            'nodes' => $query->tree(),
        ]);
    }
}
