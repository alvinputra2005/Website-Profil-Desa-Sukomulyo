<?php

namespace App\Services\Letters;

use App\Models\LetterApplication;
use Illuminate\Support\Collection;

class LetterDocumentRequirementService
{
    public function forApplication(LetterApplication $application): Collection
    {
        $formData = $application->form_data_json ?? [];

        return collect($application->service->requirements_json ?? [])
            ->filter(function (array $requirement) use ($formData): bool {
                $requiredWhen = $requirement['required_when'] ?? null;

                if (is_array($requiredWhen) && isset($requiredWhen['field'], $requiredWhen['values'])) {
                    return in_array(
                        data_get($formData, $requiredWhen['field']),
                        (array) $requiredWhen['values'],
                        true,
                    );
                }

                $requiredFor = $requirement['required_for'] ?? null;
                $applicationType = data_get($formData, 'jenis_pengajuan_ktp');

                return ! is_array($requiredFor) || in_array($applicationType, $requiredFor, true);
            })
            ->map(function (array $requirement): array {
                if (isset($requirement['required_for']) || isset($requirement['required_when'])) {
                    $requirement['required'] = true;
                }

                return $requirement;
            })
            ->values();
    }
}
