<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PopulationTrendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maximumYear = (int) config('village.population_year', now()->year);

        return [
            'from_year' => ['nullable', 'integer', 'min:1900', 'max:'.$maximumYear],
            'to_year' => ['nullable', 'integer', 'min:1900', 'max:'.$maximumYear, 'gte:from_year'],
            'sort' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
