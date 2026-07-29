<?php

namespace App\Services\Letters;

use App\Models\LetterService;

class LetterFormSchemaService
{
    private const TYPES = ['text', 'textarea', 'date', 'number', 'select', 'radio'];

    public function fields(LetterService $service): array
    {
        return collect($service->form_schema_json ?? [])->filter(fn ($field) => is_array($field)
            && isset($field['key'], $field['label'], $field['type'])
            && preg_match('/^[a-z][a-z0-9_]*$/', $field['key'])
            && in_array($field['type'], self::TYPES, true))->values()->all();
    }

    public function rules(LetterService $service): array
    {
        $rules = [];
        foreach ($this->fields($service) as $field) {
            $fieldRules = [($field['required'] ?? false) ? 'required' : 'nullable'];
            $fieldRules[] = match ($field['type']) {
                'date' => 'date',
                'number' => 'numeric',
                default => 'string',
            };
            if (isset($field['max'])) {
                $fieldRules[] = 'max:'.(int) $field['max'];
            }
            if (in_array($field['type'], ['select', 'radio'], true) && isset($field['options']) && is_array($field['options'])) {
                $fieldRules[] = 'in:'.implode(',', array_keys($field['options']));
            }
            $rules['form_data.'.$field['key']] = $fieldRules;
        }

        return $rules;
    }

    public function sanitize(LetterService $service, array $data): array
    {
        $allowed = collect($this->fields($service))->pluck('key')->all();

        return collect($data)->only($allowed)->map(fn ($value) => is_string($value) ? trim(strip_tags($value)) : $value)->all();
    }
}
