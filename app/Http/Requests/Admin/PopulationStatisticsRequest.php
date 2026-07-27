<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class PopulationStatisticsRequest extends PopulationRequest
{
    public function rules(): array
    {
        return ['category' => ['nullable', Rule::in(['sex', 'age', 'area', 'religion', 'education', 'occupation', 'marital_status', 'citizenship'])]];
    }
}
