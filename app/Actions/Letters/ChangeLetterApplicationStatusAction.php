<?php

namespace App\Actions\Letters;

use App\Enums\LetterApplicationStatus;
use App\Models\LetterApplication;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\Letters\LetterSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeLetterApplicationStatusAction
{
    public function __construct(private readonly LetterSettings $settings, private readonly ActivityLogger $logger) {}

    public function execute(LetterApplication $application, LetterApplicationStatus $target, array $data, ?User $user = null): LetterApplication
    {
        return DB::transaction(function () use ($application, $target, $data, $user) {
            $locked = LetterApplication::query()->whereKey($application->getKey())->lockForUpdate()->firstOrFail();
            if ($target === LetterApplicationStatus::Processing && $locked->documents()->where('review_status', '!=', 'approved')->exists()) {
                throw ValidationException::withMessages(['status' => 'Semua dokumen harus disetujui sebelum permohonan diproses.']);
            }
            if (! $locked->status->canTransitionTo($target)) {
                throw ValidationException::withMessages(['status' => 'Perubahan status tidak diizinkan.']);
            }
            $from = $locked->status;
            $timestamps = match ($target) {
                LetterApplicationStatus::UnderReview => ['reviewed_at' => now()],
                LetterApplicationStatus::Processing => ['processing_at' => now()],
                LetterApplicationStatus::ReadyForPickup => ['ready_at' => now()],
                LetterApplicationStatus::Completed => ['completed_at' => now(), 'tracking_expires_at' => now()->addDays($this->settings->retentionDays())],
                LetterApplicationStatus::Rejected => ['rejected_at' => now()],
                LetterApplicationStatus::Cancelled => ['cancelled_at' => now()],
                default => [],
            };
            $locked->fill([...$timestamps, 'status' => $target, 'public_note' => $data['public_note'] ?? null, 'internal_note' => $data['internal_note'] ?? $locked->internal_note, 'assigned_to' => $locked->assigned_to ?? $user?->id])->save();
            $locked->statusHistories()->create(['from_status' => $from, 'to_status' => $target, 'public_note' => $data['public_note'] ?? null, 'internal_note' => $data['internal_note'] ?? null, 'changed_by' => $user?->id, 'created_at' => now()]);
            $this->logger->log('status_changed', 'letter_applications', $locked, ['status' => $from->value], ['status' => $target->value]);

            return $locked->refresh();
        });
    }
}
