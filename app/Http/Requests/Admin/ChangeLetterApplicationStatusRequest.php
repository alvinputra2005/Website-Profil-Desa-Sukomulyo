<?php

namespace App\Http\Requests\Admin;

use App\Enums\LetterApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeLetterApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('changeStatus', $this->route('application')) ?? false;
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::enum(LetterApplicationStatus::class)], 'public_note' => [Rule::requiredIf(in_array($this->input('status'), ['revision_required', 'rejected'], true)), 'nullable', 'string', 'max:2000'], 'internal_note' => ['nullable', 'string', 'max:3000'], 'send_whatsapp' => ['nullable', 'boolean']];
    }
}
