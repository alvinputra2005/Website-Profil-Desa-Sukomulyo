<?php

namespace App\Actions\Letters;

use App\Enums\LetterApplicationStatus;
use App\Models\LetterApplication;
use App\Services\ActivityLogger;
use App\Services\Letters\LetterFormSchemaService;
use App\Services\Letters\LetterSettings;
use App\Services\Letters\SensitiveDataHasher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateLetterApplicationAction
{
    public function __construct(private readonly SensitiveDataHasher $hasher, private readonly LetterSettings $settings, private readonly LetterFormSchemaService $schemas, private readonly ActivityLogger $logger) {}

    public function execute(LetterApplication $application, array $data): LetterApplication
    {
        if (! $application->canBeEditedByApplicant()) {
            throw ValidationException::withMessages(['application' => 'Permohonan ini tidak dapat diperbaiki.']);
        }

        return DB::transaction(function () use ($application, $data) {
            $nik = $this->hasher->normalizeNik($data['applicant_nik']);
            $application->fill([...collect($data)->only(['applicant_name', 'birth_place', 'birth_date', 'sex', 'address', 'hamlet', 'rt', 'rw', 'purpose'])->all(), 'applicant_nik' => $nik, 'applicant_nik_hash' => $this->hasher->nik($nik), 'applicant_phone' => $this->settings->normalizePhone($data['applicant_phone']), 'form_data_json' => $this->schemas->sanitize($application->service, $data['form_data'] ?? []), 'status' => LetterApplicationStatus::Submitted, 'public_note' => null, 'submitted_at' => now()])->save();
            $application->statusHistories()->create(['from_status' => LetterApplicationStatus::RevisionRequired, 'to_status' => LetterApplicationStatus::Submitted, 'public_note' => 'Perbaikan telah dikirim oleh pemohon.', 'created_at' => now()]);
            $this->logger->log('revised', 'letter_applications', $application, null, ['application_number' => $application->application_number, 'status' => 'submitted']);

            return $application->refresh();
        });
    }
}
