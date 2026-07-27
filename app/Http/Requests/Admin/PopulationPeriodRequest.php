<?php

namespace App\Http\Requests\Admin;

class PopulationPeriodRequest extends PopulationRequest
{
    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'month' => ['nullable', 'integer', 'between:1,12'],
        ];
    }

    public function period(): array
    {
        $data = $this->validated();

        return [(int) ($data['year'] ?? now()->year), (int) ($data['month'] ?? now()->month)];
    }
}
