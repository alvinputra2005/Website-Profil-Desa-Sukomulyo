<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class SavePopulationGroupRequest extends PopulationRequest
{
    public function rules(): array
    {
        $group = $this->route('group');

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('population_groups')->ignore($group?->id)],
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:100'],
            'establishment_decree' => ['nullable', 'string', 'max:100'],
            'chairperson_id' => ['required', 'exists:residents,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
