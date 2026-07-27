<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class PopulationPeriodRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'month' => ['nullable', 'integer', 'between:1,12'],
        ];
    }
}
