<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PopulationStatisticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'menu' => ['nullable', 'string', Rule::in(array_keys(config('statistic_pages.population_menus', [])))],
            'indicator' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_]+$/'],
        ];
    }
}
