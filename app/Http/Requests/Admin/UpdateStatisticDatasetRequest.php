<?php

namespace App\Http\Requests\Admin;

use App\Models\StatisticDataset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStatisticDatasetRequest extends FormRequest
{
    public function authorize(): bool
    {
        $dataset = $this->route('dataset');

        return $dataset instanceof StatisticDataset
            && $this->user()?->can('update', $dataset);
    }

    public function rules(): array
    {
        /** @var StatisticDataset $dataset */
        $dataset = $this->route('dataset');
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'short_title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'period' => ['required', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'needs_review', 'archived'])],
            'rows' => ['required', 'array'],
            'rows.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('statistic_rows', 'id')
                    ->where('statistic_dataset_id', $dataset->id),
            ],
            'totals' => ['nullable', 'array'],
        ];

        foreach ($dataset->columns_json ?? [] as $column) {
            $key = (string) ($column['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $valueRules = match ($column['type'] ?? 'string') {
                'integer' => ['nullable', 'integer'],
                'percentage' => ['nullable', 'numeric'],
                default => ['nullable', 'string', 'max:1000'],
            };

            $rules["rows.*.$key"] = $valueRules;
            $rules["totals.$key"] = $valueRules;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'rows.*.id.exists' => 'Salah satu baris bukan bagian dari dataset ini.',
            'rows.*.*.integer' => 'Nilai pada kolom bilangan bulat harus berupa angka tanpa desimal.',
            'rows.*.*.numeric' => 'Nilai pada kolom persentase harus berupa angka.',
        ];
    }
}
