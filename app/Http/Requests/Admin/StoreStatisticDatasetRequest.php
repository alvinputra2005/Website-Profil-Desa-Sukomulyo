<?php

namespace App\Http\Requests\Admin;

use App\Models\StatisticDataset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStatisticDatasetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', StatisticDataset::class) ?? false;
    }

    public function rules(): array
    {
        $template = $this->template();
        $rules = [
            'template_id' => ['nullable', 'integer', Rule::exists('statistic_datasets', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'short_title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'period' => ['required', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'needs_review', 'archived'])],
            'columns' => $template ? ['nullable', 'array'] : ['required', 'array', 'min:1'],
            'columns.*.label' => ['required_with:columns', 'string', 'max:100'],
            'columns.*.key' => ['required_with:columns', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', 'distinct'],
            'columns.*.type' => ['required_with:columns', Rule::in(['string', 'integer', 'percentage'])],
            'rows' => ['nullable', 'array'],
            'totals' => ['nullable', 'array'],
        ];

        foreach ($template?->columns_json ?? [] as $column) {
            $key = (string) ($column['key'] ?? '');
            if ($key === '') continue;
            $valueRules = match ($column['type'] ?? 'string') {
                'integer' => ['nullable', 'integer'],
                'percentage' => ['nullable', 'numeric'],
                default => ['nullable', 'string', 'max:1000'],
            };
            $rules["rows.*.$key"] = $valueRules;
            $rules["totals.$key"] = $valueRules;
        }

        if (! $template) {
            $rules['rows.*.*'] = ['nullable'];
            $rules['totals.*'] = ['nullable'];
        }

        return $rules;
    }

    public function template(): ?StatisticDataset
    {
        $template = $this->route('template_dataset');
        if ($template instanceof StatisticDataset) return $template;

        return filled($this->input('template_id'))
            ? StatisticDataset::query()->find($this->integer('template_id'))
            : null;
    }
}
