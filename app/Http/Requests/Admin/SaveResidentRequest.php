<?php

namespace App\Http\Requests\Admin;

use App\Models\Resident;
use Illuminate\Validation\Rule;

class SaveResidentRequest extends PopulationRequest
{
    public function rules(): array
    {
        $resident = $this->route('resident');

        return array_merge([
            'nik' => ['required', 'digits:16', Rule::unique('residents')->ignore($resident?->id)],
            'name' => ['required', 'string', 'max:100'],
            'sex' => ['required', Rule::in(['L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'religion' => ['nullable', 'string', 'max:30'],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'citizenship' => ['required', Rule::in(['WNI', 'WNA'])],
            'education' => ['nullable', 'string', 'max:100'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'blood_type' => ['nullable', Rule::in(['A', 'B', 'AB', 'O', '-'])],
            'father_nik' => ['nullable', 'digits:16'],
            'father_name' => ['nullable', 'string', 'max:100'],
            'mother_nik' => ['nullable', 'digits:16'],
            'mother_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:150'],
            'current_address' => ['nullable', 'string', 'max:255'],
            'previous_address' => ['nullable', 'string', 'max:255'],
            'family_id' => ['nullable', 'exists:families,id'],
            'household_id' => ['nullable', 'exists:households,id'],
            'family_relationship' => ['nullable', 'string', 'max:50'],
            'household_relationship' => ['nullable', 'string', 'max:50'],
            'resident_status' => ['required', Rule::in(['permanent', 'non_permanent'])],
            'status' => ['required', Rule::in(['active', 'moved', 'deceased', 'missing'])],
            'registered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'initial_event_type' => [$resident instanceof Resident ? 'nullable' : 'required', Rule::in(['birth', 'arrival'])],
            'event_date' => [$resident instanceof Resident ? 'nullable' : 'required', 'date'],
            'reported_at' => ['nullable', 'date'],
            'destination_address' => ['nullable', 'string', 'max:255'],
            'event_cause' => ['nullable', 'string', 'max:100'],
            'event_notes' => ['nullable', 'string', 'max:2000'],
        ], $this->areaRules());
    }
}
