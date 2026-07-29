<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LetterApplication;
use App\Services\Letters\LetterWhatsAppUrlBuilder;
use Illuminate\Http\RedirectResponse;

class LetterWhatsAppConfirmationController extends Controller
{
    public function __invoke(string $token, LetterWhatsAppUrlBuilder $builder): RedirectResponse
    {
        $application = LetterApplication::query()->with('service')->where('tracking_token_hash', hash('sha256', $token))->firstOrFail();
        abort_if($application->isTrackingExpired(), 410);
        $application->forceFill(['whatsapp_confirmation_opened_at' => now()])->save();

        return redirect()->away($builder->villageConfirmation($application));
    }
}
