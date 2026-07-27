<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteOfficialsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-content') ?? false;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:officials,id'],
        ];
    }
}
