<?php

namespace App\Actions\Letters;

use App\Enums\LetterApplicationStatus;
use App\Models\LetterApplication;
use App\Models\LetterService;
use App\Services\ActivityLogger;
use App\Services\Letters\ApplicationNumberGenerator;
use App\Services\Letters\LetterFormSchemaService;
use App\Services\Letters\LetterSettings;
use App\Services\Letters\SensitiveDataHasher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateLetterApplicationAction
{
    public function __construct(
        private readonly ApplicationNumberGenerator $numbers,
        private readonly SensitiveDataHasher $hasher,
        private readonly LetterSettings $settings,
        private readonly LetterFormSchemaService $schemas,
        private readonly ActivityLogger $logger,
    ) {}

    public function execute(LetterService $service, array $data): CreatedLetterApplication
    {
        return DB::transaction(function () use ($service, $data) {
            $token = Str::random(64);
            $pin = (string) random_int(100000, 999999);
            $nik = $this->hasher->normalizeNik($data['applicant_nik']);
            $phone = $this->settings->normalizePhone($data['applicant_phone']);
            $application = LetterApplication::create([
                ...collect($data)->only(['applicant_name', 'birth_place', 'birth_date', 'sex', 'address', 'hamlet', 'rt', 'rw', 'purpose'])->all(),
                'public_id' => (string) Str::ulid(),
                'application_number' => $this->numbers->generate($service->code),
                'tracking_token_hash' => hash('sha256', $token),
                'tracking_pin_hash' => Hash::make($pin),
                'letter_service_id' => $service->id,
                'service_snapshot_json' => ['id' => $service->id, 'code' => $service->code, 'name' => $service->name, 'requirements' => collect($service->requirements_json)->pluck('label')->all(), 'processing_days' => $service->processing_days],
                'applicant_nik' => $nik,
                'applicant_nik_hash' => $this->hasher->nik($nik),
                'applicant_phone' => $phone,
                'form_data_json' => $this->schemas->sanitize($service, $data['form_data'] ?? []),
                'status' => LetterApplicationStatus::Submitted,
                'submitted_at' => now(),
            ]);
            $application->statusHistories()->create(['to_status' => LetterApplicationStatus::Submitted, 'created_at' => now()]);
            $this->logger->log('created', 'letter_applications', $application, null, ['application_number' => $application->application_number, 'status' => $application->status->value]);

            return new CreatedLetterApplication($application, $token, $pin);
        });
    }
}
