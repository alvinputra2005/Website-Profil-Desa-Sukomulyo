<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Letters\ChangeLetterApplicationStatusAction;
use App\Enums\LetterApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangeLetterApplicationStatusRequest;
use App\Models\LetterApplication;
use Illuminate\Http\RedirectResponse;

class LetterApplicationStatusController extends Controller
{
    public function update(ChangeLetterApplicationStatusRequest $request, LetterApplication $application, ChangeLetterApplicationStatusAction $action): RedirectResponse
    {
        $action->execute($application, LetterApplicationStatus::from($request->validated('status')), $request->validated(), $request->user());

        return back()->with('success', 'Status permohonan berhasil diperbarui. WhatsApp tidak dikirim otomatis.');
    }
}
