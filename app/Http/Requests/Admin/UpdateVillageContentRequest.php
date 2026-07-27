<?php

namespace App\Http\Requests\Admin;

use App\Models\VillageProfileSection;
use App\Support\ImageUploadRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVillageContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', new VillageProfileSection);
    }

    public function rules(): array
    {
        return match ($this->route('page')) {
            'profile' => array_merge([
                'site_name' => ['required', 'string', 'max:255'],
                'tagline' => ['nullable', 'string', 'max:255'],
                'village_code' => ['required', 'string', 'regex:/^[0-9]{2}\.[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/'],
                'village_bps_code' => ['nullable', 'string', 'max:20', 'regex:/^[0-9.\-\s]+$/'],
                'postal_code' => ['nullable', 'regex:/^[0-9]{5}$/'],
                'address' => ['nullable', 'string', 'max:1000'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+().\-\s]+$/'],
                'mobile' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+().\-\s]+$/'],
                'website' => ['nullable', 'url:http,https', 'max:255'],
                'district_name' => ['required', 'string', 'max:100'],
                'district_code' => ['required', 'string', 'regex:/^[0-9]{2}\.[0-9]{2}\.[0-9]{2}$/'],
                'district_head_name' => ['nullable', 'string', 'max:255'],
                'district_head_nip' => ['nullable', 'string', 'max:30'],
                'regency_name' => ['required', 'string', 'max:100'],
                'regency_code' => ['required', 'string', 'regex:/^[0-9]{2}\.[0-9]{2}$/'],
                'province_name' => ['required', 'string', 'max:100'],
                'province_code' => ['required', 'string', 'regex:/^[0-9]{2}$/'],
                'profile_content' => ['required', 'string', 'max:100000'],
                'status' => ['required', Rule::in(['draft', 'published'])],
            ], $this->imageRules('profile_image_id')),
            'vision-mission' => [
                'vision' => ['required', 'string', 'max:100000'],
                'mission' => ['required', 'string', 'max:100000'],
                'status' => ['required', Rule::in(['draft', 'published'])],
            ],
            'history', 'potential' => array_merge([
                'title' => ['required', 'string', 'max:255'],
                'content' => ['required', 'string', 'max:100000'],
                'status' => ['required', Rule::in(['draft', 'published'])],
            ], $this->imageRules('image_id')),
            default => [],
        };
    }

    private function imageRules(string $name): array
    {
        $base = str_ends_with($name, '_id') ? substr($name, 0, -3) : $name;

        return [
            $name => ['nullable', 'integer', 'exists:media,id'],
            $base.'_upload' => ImageUploadRules::optional(),
            $base.'_alt' => ['nullable', 'string', 'max:255'],
            'remove_'.$base => ['nullable', 'boolean'],
        ];
    }
}
