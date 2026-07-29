<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterApplication;
use App\Services\Letters\LetterWhatsAppUrlBuilder;
use Illuminate\Http\RedirectResponse;

class LetterApplicationWhatsAppController extends Controller
{
    public function __invoke(LetterApplication $application, LetterWhatsAppUrlBuilder $builder): RedirectResponse
    {
        $this->authorize('openWhatsApp', $application);
        $application->forceFill(['whatsapp_admin_opened_at' => now(), 'last_whatsapp_opened_by' => auth()->id()])->save();
        $trackingUrl = route('letter-services.track.form');

        return redirect()->away($builder->applicantNotification($application, $trackingUrl));
    }
}
