<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class SavePopulationYearlySnapshotRequest extends PopulationRequest
{
    public function rules(): array
    {
        $snapshot = $this->route('yearly_snapshot');

        return [
            'year' => [
                'required',
                'integer',
                'min:1900',
                'max:'.config('village.population_year'),
                Rule::unique('population_yearly_snapshots', 'year')->ignore($snapshot?->id),
            ],
            'male_count' => ['required', 'integer', 'min:0'],
            'female_count' => ['required', 'integer', 'min:0'],
            'reference_date' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }
}
