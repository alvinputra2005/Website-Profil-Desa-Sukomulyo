<?php

namespace App\Http\Controllers\Admin\Officials;

use App\Http\Controllers\Controller;
use App\Models\Official;
use App\Queries\Officials\OfficialOrganizationQuery;
use Illuminate\View\View;

class OfficialOrganizationController extends Controller
{
    public function __invoke(OfficialOrganizationQuery $query): View
    {
        $this->authorize('viewAny', Official::class);

        return view('admin.officials.organization', ['nodes' => $query->tree()]);
    }
}
