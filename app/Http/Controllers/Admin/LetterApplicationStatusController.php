<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Letters\ChangeLetterApplicationStatusAction;
use App\Enums\LetterApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangeLetterApplicationStatusRequest;
use App\Models\LetterApplication;
use App\Services\Letters\LetterWhatsAppUrlBuilder;
use Illuminate\Http\RedirectResponse;

class LetterApplicationStatusController extends Controller
{
    public function update(ChangeLetterApplicationStatusRequest $request, LetterApplication $application, ChangeLetterApplicationStatusAction $action, LetterWhatsAppUrlBuilder $whatsApp): RedirectResponse
    {
        $updated = $action->execute($application, LetterApplicationStatus::from($request->validated('status')), $request->validated(), $request->user());

        if ($request->boolean('send_whatsapp')) {
            $history = $updated->statusHistories()->latest('created_at')->first();
            $history?->update(['metadata_json' => [...($history->metadata_json ?? []), 'whatsapp_opened_at' => now()->toIso8601String(), 'whatsapp_opened_by' => $request->user()?->id]]);
            $updated->forceFill(['whatsapp_admin_opened_at' => now(), 'last_whatsapp_opened_by' => $request->user()?->id])->save();

            return redirect()->away($whatsApp->applicantNotification($updated, route('letter-services.track.form')));
        }

        return back()->with('success', 'Status permohonan berhasil diperbarui.');
    }
}
