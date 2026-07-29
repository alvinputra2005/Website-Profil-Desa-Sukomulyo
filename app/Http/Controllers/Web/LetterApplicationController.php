<?php

namespace App\Http\Controllers\Web;

use App\Actions\Letters\ChangeLetterApplicationStatusAction;
use App\Actions\Letters\CreateLetterApplicationAction;
use App\Actions\Letters\UpdateLetterApplicationAction;
use App\Enums\LetterApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreLetterApplicationRequest;
use App\Http\Requests\Web\UpdateLetterApplicationRequest;
use App\Models\LetterApplication;
use App\Models\LetterService;
use App\Services\Letters\LetterFormSchemaService;
use App\Services\Web\PublicSiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LetterApplicationController extends Controller
{
    public function create(LetterService $letterService, PublicSiteService $site, LetterFormSchemaService $schemas): View
    {
        abort_unless($letterService->is_active, 404);
        session(['letter_form_rendered_at' => now()->timestamp]);
        $submissionKey = Str::random(40);
        session(["letter_submission.{$submissionKey}" => ['service_id' => $letterService->id]]);
        $site->shareLayout();

        return view('pages.letters.create', ['letterService' => $letterService, 'fields' => $schemas->fields($letterService), 'submissionKey' => $submissionKey]);
    }

    public function store(StoreLetterApplicationRequest $request, LetterService $letterService, CreateLetterApplicationAction $action): RedirectResponse
    {
        $submissionKey = $request->validated('submission_key');
        $submission = $submissionKey ? session("letter_submission.{$submissionKey}") : null;
        if ($submissionKey) {
            abort_unless(is_array($submission) && (int) ($submission['service_id'] ?? 0) === $letterService->id, 419);
            if (isset($submission['token'])) {
                return redirect()->route('letter-services.track.token', $submission['token']);
            }
        }
        $created = $action->execute($letterService, $request->validated());
        if ($submissionKey) {
            session(["letter_submission.{$submissionKey}" => ['service_id' => $letterService->id, 'token' => $created->trackingToken]]);
        }

        return redirect()->route('letter-services.track.token', $created->trackingToken)
            ->with('tracking_pin', $created->trackingPin)
            ->with('new_application', true);
    }

    public function edit(string $token, PublicSiteService $site, LetterFormSchemaService $schemas): View
    {
        $application = $this->fromToken($token);
        abort_unless($application->canBeEditedByApplicant(), 403);
        $site->shareLayout();

        return view('pages.letters.edit', ['application' => $application, 'token' => $token, 'fields' => $schemas->fields($application->service)]);
    }

    public function update(UpdateLetterApplicationRequest $request, string $token, UpdateLetterApplicationAction $action): RedirectResponse
    {
        $application = $this->fromToken($token);
        $action->execute($application, $request->validated());

        return redirect()->route('letter-services.track.token', $token)->with('success', 'Perbaikan permohonan berhasil dikirim.');
    }

    public function cancel(string $token, ChangeLetterApplicationStatusAction $action): RedirectResponse
    {
        $application = $this->fromToken($token);
        $action->execute($application, LetterApplicationStatus::Cancelled, ['public_note' => 'Dibatalkan oleh pemohon.']);

        return redirect()->route('letter-services.track.token', $token)->with('success', 'Permohonan berhasil dibatalkan.');
    }

    private function fromToken(string $token): LetterApplication
    {
        return LetterApplication::query()->with('service')->where('tracking_token_hash', hash('sha256', $token))->firstOrFail();
    }
}
