<?php

namespace App\Http\Requests\Admin;

use App\Support\InventoryCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveInventoryItemRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('manage-data') === true; }

    public function rules(): array
    {
        $category = (string) $this->route('category');
        $item = $this->route('item');
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'item_code' => ['required', 'string', 'max:64'],
            'register_number' => [
                'required', 'string', 'max:64',
                Rule::unique('inventory_items')->where(fn ($query) => $query
                    ->where('category', $category)->where('item_code', $this->input('item_code')))
                    ->ignore($item?->id),
            ],
            'acquisition_year' => ['required', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'origin' => ['required', Rule::in(InventoryCategory::ORIGINS)],
            'value' => ['required', 'numeric', 'min:0', 'max:999999999999999999.99'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'condition' => ['required', Rule::in(InventoryCategory::CONDITIONS)],
            'notes' => ['nullable', 'string', 'max:10000'],
            'details' => ['nullable', 'array'],
        ];

        foreach (InventoryCategory::get($category)['fields'] ?? [] as $key => [$label, $type, $required]) {
            $fieldRules = [$required ? 'required' : 'nullable'];
            $fieldRules[] = $type === 'number' ? 'numeric' : ($type === 'date' ? 'date' : 'string');
            if ($type === 'number') $fieldRules[] = 'min:0';
            if ($type !== 'number' && $type !== 'date') $fieldRules[] = 'max:255';
            $rules['details.'.$key] = $fieldRules;
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'name' => 'nama barang', 'item_code' => 'kode barang', 'register_number' => 'nomor register',
            'acquisition_year' => 'tahun pengadaan', 'origin' => 'asal-usul', 'value' => 'harga/nilai',
            'quantity' => 'jumlah', 'condition' => 'kondisi', 'notes' => 'keterangan',
        ];
        foreach (InventoryCategory::get((string) $this->route('category'))['fields'] ?? [] as $key => [$label]) {
            $attributes['details.'.$key] = strtolower($label);
        }
        return $attributes;
    }
}
