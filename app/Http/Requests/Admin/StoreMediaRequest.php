<?php

namespace App\Http\Requests\Admin;

use App\Models\Media;
use App\Support\ImageUploadRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Media::class);
    }

    public function rules(): array
    {
        $fileRules = ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,csv'];
        if ($this->file('file') && str_starts_with((string) $this->file('file')->getMimeType(), 'image/')) {
            $fileRules = ImageUploadRules::required();
        }

        return [
            'file' => $fileRules,
            'category' => ['nullable', 'in:news,gallery,officials,banners,documents'],
            'folder_name' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
