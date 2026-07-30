<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessStatisticImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-data') ?? false;
    }

    public function rules(): array
    {
        return [
            'datasets' => ['required', 'array', 'min:1'],
            'datasets.*.dataset_id' => ['required', 'string', 'max:150'],
            'datasets.*.selected' => ['nullable', 'boolean'],
            'datasets.*.category_slug' => [
                'required',
                'string',
                Rule::exists('statistic_categories', 'slug')->where('is_active', true),
            ],
            'datasets.*.short_title' => ['nullable', 'string', 'max:255'],
            'datasets.*.status' => ['required', Rule::in(['draft', 'published'])],
        ];
    }
}
