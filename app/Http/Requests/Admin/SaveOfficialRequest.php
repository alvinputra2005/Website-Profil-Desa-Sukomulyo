<?php

namespace App\Http\Requests\Admin;

use App\Models\Official;
use App\Support\ImageUploadRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOfficialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-content') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('source')) {
            $this->merge(['source' => 'external']);
        }
    }

    public function rules(): array
    {
        $official = $this->route('official');
        $excludedSuperiors = $official instanceof Official
            ? array_merge([$official->id], $this->descendantIds($official))
            : [];

        return [
            'source' => ['required', Rule::in(['resident', 'external'])],
            'resident_id' => ['nullable', 'required_if:source,resident', 'exists:residents,id'],
            'name' => ['required', 'string', 'max:255'],
            'title_prefix' => ['nullable', 'string', 'max:50'],
            'title_suffix' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'digits:16'],
            'village_employee_number' => ['nullable', 'string', 'max:25'],
            'nip' => ['nullable', 'string', 'max:30'],
            'id_card_tag' => ['nullable', 'string', 'max:50', Rule::unique('officials')->ignore($official?->id)],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'sex' => ['nullable', Rule::in(['L', 'P'])],
            'education' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:30'],
            'rank_grade' => ['nullable', 'string', 'max:50'],
            'position' => ['required', 'string', 'max:255'],
            'appointment_decree' => ['nullable', 'string', 'max:100'],
            'appointment_date' => ['nullable', 'date'],
            'dismissal_decree' => ['nullable', 'string', 'max:100'],
            'dismissal_date' => ['nullable', 'date', 'after_or_equal:appointment_date'],
            'term' => ['nullable', 'string', 'max:150'],
            'is_acting' => ['nullable', 'boolean'],
            'superior_id' => ['nullable', 'exists:officials,id', Rule::notIn($excludedSuperiors)],
            'organization_level' => ['nullable', 'integer', 'min:1', 'max:20'],
            'organization_offset' => ['nullable', 'integer', 'between:-100,100'],
            'organization_layout' => ['nullable', Rule::in(['hanging', 'horizontal'])],
            'organization_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'photo_id' => ['nullable', 'exists:media,id'],
            'photo_upload' => ImageUploadRules::optional(),
            'photo_camera' => ImageUploadRules::optional(),
            'photo_alt' => ['nullable', 'string', 'max:255'],
            'remove_photo' => ['nullable', 'boolean'],
            'biography' => ['nullable', 'string', 'max:5000'],
            'display_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'can_sign_on_behalf' => ['nullable', 'boolean'],
            'can_sign_for' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:150'],
            'facebook' => ['nullable', 'url:http,https', 'max:500'],
            'instagram' => ['nullable', 'url:http,https', 'max:500'],
            'youtube' => ['nullable', 'url:http,https', 'max:500'],
            'x' => ['nullable', 'url:http,https', 'max:500'],
            'registered_at' => ['nullable', 'date'],
        ];
    }

    private function descendantIds(Official $official): array
    {
        $ids = [];
        $pending = [$official->id];
        while ($pending) {
            $children = Official::whereIn('superior_id', $pending)->pluck('id')->all();
            $children = array_values(array_diff($children, $ids));
            if (! $children) {
                break;
            }
            $ids = array_merge($ids, $children);
            $pending = $children;
        }

        return $ids;
    }
}
