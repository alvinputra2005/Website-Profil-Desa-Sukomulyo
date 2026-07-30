<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['latest', 'oldest', 'most_downloaded'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => trim((string) $this->query('q')),
            'sort' => $this->query('sort', 'latest'),
        ]);
    }

    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => $validated['q'] !== '' ? $validated['q'] : null,
            'sort' => $validated['sort'] ?? 'latest',
        ];
    }
}
