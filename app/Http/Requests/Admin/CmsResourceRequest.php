<?php

namespace App\Http\Requests\Admin;

use App\Support\ImageUploadRules;
use Illuminate\Foundation\Http\FormRequest;

class CmsResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $config = $this->resourceConfig();
        if (! $config) {
            return false;
        }

        $id = $this->route('id');
        if ($id !== null) {
            $model = $config['model']::find($id);

            return $model !== null && $this->user()->can('update', $model);
        }

        return $this->user()->can('create', $config['model']);
    }

    public function rules(): array
    {
        $config = $this->resourceConfig();
        if (! $config) {
            return [];
        }

        $id = (string) ($this->route('id') ?? 'NULL');
        $rules = [];

        foreach ($config['fields'] as $key => $field) {
            $rule = str_replace('{id}', $id, $field['rules'] ?? 'nullable');
            if (($field['type'] ?? '') === 'image' && $this->hasFile($this->imageBase($key).'_upload')) {
                $rule = preg_replace('/(^|\|)required(?=\||$)/', '${1}nullable', $rule);
            }
            $rules[$key] = $rule;

            if (($field['type'] ?? '') === 'image') {
                $base = $this->imageBase($key);
                $rules[$base.'_upload'] = ImageUploadRules::optional();
                $rules[$base.'_alt'] = ['nullable', 'string', 'max:255'];
                $rules['remove_'.$base] = ['nullable', 'boolean'];
            }
        }

        if ($this->route('resource') === 'galleries') {
            $rules += [
                'gallery_items' => ['nullable', 'array'],
                'gallery_items.*.caption' => ['nullable', 'string', 'max:2000'],
                'gallery_items.*.display_order' => ['required', 'integer', 'min:0'],
                'remove_gallery_items' => ['nullable', 'array'],
                'remove_gallery_items.*' => ['integer'],
                'gallery_item_uploads' => ['nullable', 'array', 'max:5'],
                'gallery_item_uploads.*' => ImageUploadRules::required(),
                'gallery_item_captions' => ['nullable', 'array'],
                'gallery_item_captions.*' => ['nullable', 'string', 'max:2000'],
                'gallery_sequence' => ['nullable', 'array'],
                'gallery_sequence.*' => ['string', 'max:40'],
                'gallery_item_upload' => ImageUploadRules::optional(),
                'gallery_item_caption' => ['nullable', 'string', 'max:2000'],
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'gallery_item_uploads.max' => 'Maksimal 5 gambar dapat diunggah per sekali simpan.',
            'gallery_item_uploads.*.dimensions' => 'Gambar ke-:position memiliki resolusi terlalu besar. Maksimal 4000 × 4000 piksel.',
            'gallery_item_uploads.*.max' => 'Gambar ke-:position berukuran lebih dari 5 MB.',
            'gallery_item_uploads.*.image' => 'File ke-:position bukan gambar yang valid.',
            'gallery_item_uploads.*.mimes' => 'Gambar ke-:position harus berformat JPG, PNG, atau WebP.',
        ];
    }

    public function resourceData(): array
    {
        return collect($this->validated())
            ->only(array_keys($this->resourceConfig()['fields']))
            ->all();
    }

    private function resourceConfig(): ?array
    {
        return config('admin.resources.'.$this->route('resource'));
    }

    private function imageBase(string $name): string
    {
        return str_ends_with($name, '_id') ? substr($name, 0, -3) : $name;
    }
}
