<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDeleteStatisticDatasetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-data') ?? false;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                Rule::exists('statistic_datasets', 'id')->whereNull('deleted_at'),
            ],
        ];
    }
}
