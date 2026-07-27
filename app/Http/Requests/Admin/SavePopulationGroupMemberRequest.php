<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class SavePopulationGroupMemberRequest extends PopulationRequest
{
    public function rules(): array
    {
        $group = $this->route('group');
        $membership = $this->route('membership');

        return [
            'resident_id' => ['required', 'exists:residents,id', Rule::unique('population_group_members')->where('group_id', $group->id)->ignore($membership?->id)],
            'member_number' => ['nullable', 'string', 'max:30', Rule::unique('population_group_members')->where('group_id', $group->id)->ignore($membership?->id)],
            'position' => ['required', 'string', 'max:50'],
            'appointment_decree' => ['nullable', 'string', 'max:100'],
            'appointment_date' => ['nullable', 'date'],
            'dismissal_decree' => ['nullable', 'string', 'max:100'],
            'dismissal_date' => ['nullable', 'date', 'after_or_equal:appointment_date'],
            'period' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
