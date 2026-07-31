<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveApbdesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('manage-data');
    }

    public function rules(): array
    {
        $apbdes = $this->route('apbdes');

        return [
            'year' => [
                'required',
                'integer',
                'min:1900',
                'max:2100',
                Rule::unique('apbdes', 'year')->ignore($apbdes?->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_partial_year' => ['nullable', 'boolean'],
            'income_budget' => ['required', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'income_realization' => ['required', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'spending_budget' => ['required', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'spending_realization' => ['required', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'financing_receipt' => ['required', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'financing_expenditure' => ['required', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'revenue' => ['required', 'array', 'min:1', 'max:50'],
            'revenue.*.code' => ['nullable', 'string', 'max:30'],
            'revenue.*.name' => ['required', 'string', 'max:255'],
            'revenue.*.short_name' => ['nullable', 'string', 'max:100'],
            'revenue.*.budget' => ['required', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'revenue.*.realization' => ['required', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'revenue.*.note' => ['nullable', 'string', 'max:2000'],
            'spending' => ['required', 'array', 'min:1', 'max:30'],
            'spending.*.code' => ['nullable', 'string', 'max:30'],
            'spending.*.name' => ['required', 'string', 'max:255'],
            'spending.*.short_name' => ['nullable', 'string', 'max:100'],
            'spending.*.description' => ['nullable', 'string', 'max:2000'],
            'spending.*.budget' => ['required', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'spending.*.realization' => ['required', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'spending.*.note' => ['nullable', 'string', 'max:2000'],
            'programs' => ['nullable', 'array', 'max:200'],
            'programs.*.code' => ['nullable', 'string', 'max:50'],
            'programs.*.category_code' => ['nullable', 'string', 'max:30'],
            'programs.*.category' => ['nullable', 'string', 'max:255'],
            'programs.*.name' => ['nullable', 'string', 'max:255'],
            'programs.*.description' => ['nullable', 'string', 'max:2000'],
            'programs.*.budget' => ['nullable', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'programs.*.realization' => ['nullable', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'problems' => ['nullable', 'string', 'max:20000'],
            'solutions' => ['nullable', 'string', 'max:20000'],
            'programs_note' => ['nullable', 'string', 'max:2000'],
            'quarters_note' => ['nullable', 'string', 'max:2000'],
            'data_quality_notes' => ['nullable', 'string', 'max:10000'],
            'source_document' => ['nullable', 'string', 'max:255'],
            'source_reference' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'income_budget' => 'target pendapatan',
            'income_realization' => 'realisasi pendapatan',
            'spending_budget' => 'anggaran belanja',
            'spending_realization' => 'realisasi belanja',
            'financing_receipt' => 'penerimaan pembiayaan',
            'financing_expenditure' => 'pengeluaran pembiayaan',
            'revenue.*.name' => 'uraian pendapatan',
            'revenue.*.budget' => 'target rincian pendapatan',
            'revenue.*.realization' => 'realisasi rincian pendapatan',
            'spending.*.name' => 'bidang belanja',
            'spending.*.budget' => 'anggaran bidang belanja',
            'spending.*.realization' => 'realisasi bidang belanja',
        ];
    }
}
