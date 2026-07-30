<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LinkLetterApplicationResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('linkResident', $this->route('application')) ?? false;
    }

    public function rules(): array
    {
        return ['resident_id' => ['nullable', 'exists:residents,id']];
    }
}
