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
        ];
    }
}
