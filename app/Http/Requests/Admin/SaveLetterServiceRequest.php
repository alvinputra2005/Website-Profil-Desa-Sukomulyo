<?php

namespace App\Http\Requests\Admin;

use App\Models\LetterService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveLetterServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->route('letterService') ? 'update' : 'create', $this->route('letterService') ?? LetterService::class) ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('letterService')?->id;

        return ['name' => ['required', 'string', 'max:150'], 'code' => ['required', 'alpha_num', 'max:20', Rule::unique('letter_services')->ignore($id)], 'description' => ['required', 'string', 'max:5000'], 'icon' => ['required', 'string', Rule::in(array_keys(config('letter_services.icons', [])))], 'requirements' => ['required', 'array', 'min:1'], 'requirements.*.code' => ['required', 'alpha_dash', 'max:50'], 'requirements.*.label' => ['required', 'string', 'max:150'], 'requirements.*.description' => ['nullable', 'string', 'max:500'], 'requirements.*.required' => ['nullable', 'boolean'], 'processing_days' => ['required', 'integer', 'min:1', 'max:30'], 'fee_information' => ['required', 'string', 'max:100'], 'pickup_instructions' => ['nullable', 'string', 'max:3000'], 'is_active' => ['nullable', 'boolean'], 'display_order' => ['required', 'integer', 'min:0']];
    }
}
