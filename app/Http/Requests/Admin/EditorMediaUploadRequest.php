<?php

namespace App\Http\Requests\Admin;

use App\Models\Media;
use App\Support\ImageUploadRules;
use Illuminate\Foundation\Http\FormRequest;

class EditorMediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Media::class);
    }

    public function rules(): array
    {
        return [
            'image' => ImageUploadRules::required(),
            'folder_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
