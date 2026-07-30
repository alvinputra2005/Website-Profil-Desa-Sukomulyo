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
            'per_page' => ['nullable', 'integer', Rule::in([5, 10, 25, 50])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => trim((string) $this->query('q')),
            'sort' => $this->query('sort', 'latest'),
            'per_page' => $this->query('per_page', 10),
        ]);
    }

    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => $validated['q'] !== '' ? $validated['q'] : null,
            'sort' => $validated['sort'] ?? 'latest',
            'per_page' => (int) ($validated['per_page'] ?? 10),
        ];
    }
}
