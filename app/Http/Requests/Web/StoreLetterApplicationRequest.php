<?php

namespace App\Http\Requests\Web;

use App\Models\LetterService;
use App\Models\PopulationArea;
use App\Services\Letters\LetterFormSchemaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLetterApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('letterService') instanceof LetterService && $this->route('letterService')->is_active;
    }

    public function rules(): array
    {
        $service = $this->route('letterService');

        return [
            'applicant_name' => ['required', 'string', 'min:3', 'max:150'],
            'applicant_nik' => ['required', 'digits:16'],
            'applicant_phone' => ['required', 'string', 'regex:/^(?:\+?62|0)[0-9]{8,13}$/'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', 'in:L,P'],
            'address' => ['required', 'string', 'min:10', 'max:2000'],
            'hamlet' => ['required', 'string', Rule::in(
                collect(['Gumul', 'Talasan', 'Bakir', 'Kedungrejo', 'Biyan'])
                    ->concat(PopulationArea::query()->whereNotNull('hamlet')->distinct()->pluck('hamlet'))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all()
            )],
            'rt' => ['required', 'digits_between:1,3'],
            'rw' => ['required', 'digits_between:1,3'],
            'purpose' => ['required', 'string', 'min:5', 'max:2000'],
            'declaration' => ['accepted'],
            'website' => ['nullable', 'max:0'],
            'submission_key' => ['nullable', 'string', 'max:100'],
            ...($service instanceof LetterService ? app(LetterFormSchemaService::class)->rules($service) : []),
        ];
    }

    public function attributes(): array
    {
        return ['applicant_name' => 'nama pemohon', 'applicant_nik' => 'NIK', 'applicant_phone' => 'nomor WhatsApp', 'address' => 'alamat', 'purpose' => 'keperluan', 'declaration' => 'pernyataan'];
    }
}
