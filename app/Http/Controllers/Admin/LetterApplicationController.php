<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LinkLetterApplicationResidentRequest;
use App\Http\Requests\Admin\ReviewLetterDocumentRequest;
use App\Models\LetterApplication;
use App\Models\LetterService;
use App\Models\Resident;
use App\Models\User;
use App\Queries\Letters\AdminLetterApplicationIndexQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

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
        $application->load(['service', 'assignee', 'resident', 'statusHistories.actor', 'documents']);

        return view('admin.letter-applications.show', ['application' => $application, 'residents' => Resident::query()->orderBy('name')->limit(100)->get()]);
    }

    public function document(LetterApplication $application, int $document)
    {
        $this->authorize('view', $application);
        $file = $application->documents()->findOrFail($document);
        abort_unless(Storage::disk($file->disk)->exists($file->path), 404);
        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    public function reviewDocument(ReviewLetterDocumentRequest $request, LetterApplication $application, int $document): RedirectResponse
    {
        $this->authorize('view', $application);
        $file = $application->documents()->findOrFail($document);
        $file->update([...$request->validated(), 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        return back()->with('success', 'Status dokumen diperbarui.');
    }

    public function linkResident(LinkLetterApplicationResidentRequest $request, LetterApplication $application): RedirectResponse
    {
        $application->update(['resident_id' => $request->validated('resident_id')]);

        return back()->with('success', 'Keterhubungan data penduduk diperbarui.');
    }
}
