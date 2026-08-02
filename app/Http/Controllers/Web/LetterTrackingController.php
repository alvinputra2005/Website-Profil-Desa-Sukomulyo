<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\TrackLetterApplicationRequest;
use App\Models\LetterApplication;
use App\Services\Letters\LetterSettings;
use App\Services\Web\PublicSiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LetterTrackingController extends Controller
{
    public function create(PublicSiteService $site): View
    {
        $site->shareLayout();

        return view('pages.letters.track-form');
    }

    public function store(TrackLetterApplicationRequest $request): RedirectResponse
    {
        $application = LetterApplication::query()->where('application_number', $request->validated('application_number'))->first();
        if (! $application || $application->isTrackingExpired()) {
            throw ValidationException::withMessages(['application_number' => 'Nomor pelacakan tidak ditemukan atau sudah kedaluwarsa.']);
        }
        session(["letter_tracking.{$application->public_id}" => now()->addMinutes(30)->timestamp]);

        return redirect()->route('letter-services.track.session', $application);
    }

    public function showSession(LetterApplication $application, PublicSiteService $site, LetterSettings $settings): View
    {
        $expires = (int) session("letter_tracking.{$application->public_id}", 0);
        abort_unless($expires >= now()->timestamp && ! $application->isTrackingExpired(), 403);

        return $this->view($application, $site, $settings);
    }

    public function showToken(string $token, PublicSiteService $site, LetterSettings $settings): View
    {
        $application = LetterApplication::query()->with(['service', 'statusHistories'])->where('tracking_token_hash', hash('sha256', $token))->firstOrFail();
        abort_if($application->isTrackingExpired(), 410);

        return $this->view($application, $site, $settings, $token);
    }

    private function view(LetterApplication $application, PublicSiteService $site, LetterSettings $settings, ?string $token = null): View
    {
        $site->shareLayout();

        return view('pages.letters.track-show', compact('application', 'settings', 'token'));
    }
}
