<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class TrackLetterApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['application_number' => strtoupper(trim((string) $this->application_number))]);
    }

    public function rules(): array
    {
        return ['application_number' => ['required', 'string', 'max:40'], 'pin' => ['required', 'digits:6']];
    }
}
