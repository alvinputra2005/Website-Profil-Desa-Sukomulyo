<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStatisticImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-data') ?? false;
    }

    public function rules(): array
    {
        return [
            'statistics_file' => [
                'required',
                'file',
                'max:20480',
                'extensions:json',
                'mimetypes:application/json,text/plain,application/octet-stream',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'statistics_file.required' => 'Pilih file JSON statistik.',
            'statistics_file.extensions' => 'File statistik harus menggunakan ekstensi .json.',
            'statistics_file.mimetypes' => 'File yang dipilih tidak dikenali sebagai JSON.',
            'statistics_file.max' => 'Ukuran file JSON maksimal 20 MB.',
        ];
    }
}
