<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class SaveFamilyRequest extends PopulationRequest
{
    public function rules(): array
    {
        $family = $this->route('family');
        $headResidentRules = $family?->exists
            ? [
                'required',
                Rule::exists('residents', 'id')->where('family_id', $family->id),
                Rule::unique('families')->ignore($family->id),
            ]
            : ['prohibited'];

        return array_merge([
            'family_card_number' => ['required', 'digits:16', Rule::unique('families')->ignore($family?->id)],
            'head_resident_id' => $headResidentRules,
            'address' => ['nullable', 'string', 'max:255'],
            'social_class' => ['nullable', 'string', 'max:50'],
            'registered_at' => ['nullable', 'date'],
            'issued_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ], $this->areaRules());
    }
}
