<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveInventoryMutationRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('manage-data') === true; }

    public function rules(): array
    {
        return [
            'asset_status' => ['required', Rule::in(['Baik', 'Rusak', 'Diperbaiki', 'Hapus'])],
            'mutation_type' => ['required', Rule::in([
                'Status Baik', 'Status Rusak', 'Perbaikan', 'Masih Baik Disumbangkan',
                'Barang Rusak Disumbangkan', 'Masih Baik Dijual', 'Barang Rusak Dijual',
                'Hibah', 'Musnah', 'Hilang',
            ])],
            'mutation_date' => ['required', 'date', 'before_or_equal:today'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'required_if:mutation_type,Masih Baik Dijual,Barang Rusak Dijual'],
            'recipient' => ['nullable', 'string', 'max:255', 'required_if:mutation_type,Masih Baik Disumbangkan,Barang Rusak Disumbangkan,Hibah'],
            'notes' => ['required', 'string', 'max:10000'],
        ];
    }
}
