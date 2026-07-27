<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class SaveHouseholdRequest extends PopulationRequest
{
    public function rules(): array
    {
        $household = $this->route('household');

        return array_merge([
            'household_number' => ['required', 'regex:/^(?=.*\d)[A-Za-z0-9-]{1,30}$/', Rule::unique('households')->ignore($household?->id)],
            'head_resident_id' => ['required', 'exists:residents,id', Rule::unique('households')->ignore($household?->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'social_class' => ['nullable', 'string', 'max:50'],
            'is_dtks_registered' => ['nullable', 'boolean'],
            'dtks_reference' => ['nullable', 'string', 'max:30'],
            'registered_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ], $this->areaRules());
    }
}
