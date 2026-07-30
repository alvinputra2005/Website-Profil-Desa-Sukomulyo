<?php

namespace App\Http\Requests\Web;

use App\Models\LetterApplication;
use App\Services\Letters\LetterFormSchemaService;

class UpdateLetterApplicationRequest extends StoreLetterApplicationRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $token = (string) $this->route('token');
        $application = LetterApplication::query()->with('service')->where('tracking_token_hash', hash('sha256', $token))->first();
        $rules = parent::rules();
        if ($application) {
            $rules = [...collect($rules)->reject(fn ($value, $key) => str_starts_with($key, 'form_data.'))->all(), ...app(LetterFormSchemaService::class)->rules($application->service)];
        }

        return $rules;
    }
}
