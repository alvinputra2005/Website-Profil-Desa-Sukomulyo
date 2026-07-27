<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

abstract class PopulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-data') ?? false;
    }

    protected function areaRules(): array
    {
        return [
            'hamlet' => ['nullable', 'string', 'max:100', 'required_with:rw,rt'],
            'rw' => ['nullable', 'digits_between:1,3'],
            'rt' => ['nullable', 'digits_between:1,3'],
        ];
    }
}
