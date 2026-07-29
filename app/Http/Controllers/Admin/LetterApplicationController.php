<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LinkLetterApplicationResidentRequest;
use App\Models\LetterApplication;
use App\Models\LetterService;
use App\Models\Resident;
use App\Models\User;
use App\Queries\Letters\AdminLetterApplicationIndexQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LetterApplicationController extends Controller
{
    public function index(Request $request, AdminLetterApplicationIndexQuery $query): View
    {
        $this->authorize('viewAny', LetterApplication::class);

        return view('admin.letter-applications.index', ['applications' => $query->paginate($request), 'services' => LetterService::orderBy('name')->get(), 'officers' => User::whereHas('role', fn ($q) => $q->where('code', 'admin_data'))->get()]);
    }

    public function show(LetterApplication $application): View
    {
        $this->authorize('view', $application);
        $application->load(['service', 'assignee', 'resident', 'statusHistories.actor']);

        return view('admin.letter-applications.show', ['application' => $application, 'residents' => Resident::query()->orderBy('name')->limit(100)->get()]);
    }

    public function linkResident(LinkLetterApplicationResidentRequest $request, LetterApplication $application): RedirectResponse
    {
        $application->update(['resident_id' => $request->validated('resident_id')]);

        return back()->with('success', 'Keterhubungan data penduduk diperbarui.');
    }
}
