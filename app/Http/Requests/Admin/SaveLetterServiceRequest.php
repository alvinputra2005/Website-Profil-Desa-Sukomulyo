<?php

namespace App\Http\Requests\Admin;

use App\Models\LetterService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveLetterServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->route('letterService') ? 'update' : 'create', $this->route('letterService') ?? LetterService::class) ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('letterService')?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'alpha_num', 'max:20', Rule::unique('letter_services')->ignore($id)],
            'description' => ['required', 'string', 'max:5000'],
            'icon' => ['required', 'string', Rule::in(array_keys(config('letter_services.icons', [])))],
            'requirements' => ['required', 'array', 'min:1'],
            'requirements.*.code' => ['required', 'alpha_dash', 'max:50', 'distinct'],
            'requirements.*.label' => ['required', 'string', 'max:150'],
            'requirements.*.description' => ['nullable', 'string', 'max:500'],
            'requirements.*.required' => ['nullable', 'boolean'],
            'requirements.*.condition_field' => ['nullable', 'string', 'regex:/^[a-z][a-z0-9_]*$/', 'max:100', 'required_with:requirements.*.condition_values'],
            'requirements.*.condition_values' => ['nullable', 'string', 'max:1000', 'required_with:requirements.*.condition_field'],
            'form_fields_present' => ['nullable', 'boolean'],
            'form_fields' => ['nullable', 'array', 'max:30'],
            'form_fields.*.key' => ['required', 'string', 'regex:/^[a-z][a-z0-9_]*$/', 'max:100', 'distinct'],
            'form_fields.*.label' => ['required', 'string', 'max:150'],
            'form_fields.*.type' => ['required', Rule::in(array_keys(config('letter_services.field_types', [])))],
            'form_fields.*.options' => ['nullable', 'string', 'max:5000'],
            'form_fields.*.min' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'form_fields.*.max' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'form_fields.*.required' => ['nullable', 'boolean'],
            'processing_days' => ['required', 'integer', 'min:1', 'max:30'],
            'fee_information' => ['required', 'string', 'max:100'],
            'pickup_instructions' => ['nullable', 'string', 'max:3000'],
            'is_active' => ['nullable', 'boolean'],
            'display_order' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $fields = collect($this->input('form_fields', []))->keyBy('key');

            foreach ($fields as $index => $field) {
                $type = $field['type'] ?? null;
                $options = $this->parseOptions((string) ($field['options'] ?? ''));

                if (in_array($type, ['select', 'radio'], true) && $options === null) {
                    $position = collect($this->input('form_fields', []))->search(fn (array $candidate): bool => ($candidate['key'] ?? null) === $index);
                    $validator->errors()->add('form_fields.'.$position.'.options', 'Opsi wajib diisi dengan format nilai=Label, satu opsi per baris.');
                }

                if (isset($field['min'], $field['max']) && (int) $field['min'] > (int) $field['max']) {
                    $position = collect($this->input('form_fields', []))->search(fn (array $candidate): bool => ($candidate['key'] ?? null) === $index);
                    $validator->errors()->add('form_fields.'.$position.'.max', 'Nilai maksimum tidak boleh lebih kecil dari minimum.');
                }

                if (! in_array($type, ['text', 'textarea', 'number'], true) && (($field['min'] ?? '') !== '' || ($field['max'] ?? '') !== '')) {
                    $position = collect($this->input('form_fields', []))->search(fn (array $candidate): bool => ($candidate['key'] ?? null) === $index);
                    $validator->errors()->add('form_fields.'.$position.'.min', 'Batas minimum dan maksimum hanya berlaku untuk field teks atau angka.');
                }
            }

            $availableFields = $fields;
            if ($availableFields->isEmpty() && ! $this->boolean('form_fields_present')) {
                $availableFields = collect($this->route('letterService')?->form_schema_json ?? [])->keyBy('key');
            }

            foreach ($this->input('requirements', []) as $index => $requirement) {
                $conditionField = trim((string) ($requirement['condition_field'] ?? ''));
                $conditionValues = $this->parseConditionValues((string) ($requirement['condition_values'] ?? ''));

                if ($conditionField === '') {
                    continue;
                }

                $targetField = $availableFields->get($conditionField);
                if (! is_array($targetField)) {
                    $validator->errors()->add("requirements.{$index}.condition_field", 'Field kondisi harus tersedia pada konfigurasi formulir.');

                    continue;
                }

                $rawOptions = $targetField['options'] ?? [];
                $targetOptions = is_array($rawOptions)
                    ? $rawOptions
                    : ($this->parseOptions((string) $rawOptions) ?? []);
                if ($targetOptions !== [] && array_diff($conditionValues, array_keys($targetOptions)) !== []) {
                    $validator->errors()->add("requirements.{$index}.condition_values", 'Nilai kondisi harus menggunakan nilai opsi dari field formulir yang dipilih.');
                }
            }
        });
    }

    private function parseOptions(string $raw): ?array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($raw)) ?: [];
        $options = [];

        foreach (array_filter($lines, fn (string $line): bool => trim($line) !== '') as $line) {
            $parts = array_map('trim', explode('=', $line, 2));
            if (count($parts) !== 2 || ! preg_match('/^[A-Za-z0-9_-]+$/', $parts[0]) || $parts[1] === '' || isset($options[$parts[0]])) {
                return null;
            }
            $options[$parts[0]] = $parts[1];
        }

        return $options === [] ? null : $options;
    }

    private function parseConditionValues(string $raw): array
    {
        return collect(explode(',', $raw))->map(fn (string $value): string => trim($value))->filter()->unique()->values()->all();
    }
}
