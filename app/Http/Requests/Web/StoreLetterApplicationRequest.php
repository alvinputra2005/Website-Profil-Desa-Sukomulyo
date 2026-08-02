<?php

namespace App\Http\Requests\Web;

use App\Models\LetterService;
use App\Models\PopulationArea;
use App\Services\Letters\LetterFormSchemaService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
                    ->map(fn ($hamlet): string => mb_convert_case(trim((string) $hamlet), MB_CASE_TITLE, 'UTF-8'))
                    ->filter()
                    ->reject(fn (string $hamlet): bool => $hamlet === 'Sukomulyo')
                    ->unique(fn (string $hamlet): string => mb_strtolower($hamlet))
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

    public function after(): array
    {
        return [function (Validator $validator): void {
            $serviceCode = $this->route('letterService')?->code;

            if ($serviceCode === 'KTP' && $this->input('form_data.jenis_pengajuan_ktp') === 'baru') {
                $birthDate = $this->input('birth_date');
                $maritalStatus = $this->input('form_data.status_perkawinan');

                if ($this->validDate($birthDate) && Carbon::parse($birthDate)->age < 17 && $maritalStatus === 'belum_kawin') {
                    $validator->errors()->add(
                        'birth_date',
                        'Pengajuan KTP-el baru hanya untuk pemohon berusia 17 tahun atau lebih, sudah kawin, atau pernah kawin.'
                    );
                }
            }

            if ($serviceCode === 'SIPP') {
                $startDate = $this->input('form_data.tanggal_mulai');
                $endDate = $this->input('form_data.tanggal_selesai');

                if ($this->validDate($startDate) && $this->validDate($endDate) && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
                    $validator->errors()->add('form_data.tanggal_selesai', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
                }
            }

            if ($serviceCode === 'KIA') {
                $childNik = (string) $this->input('form_data.nik_anak');
                $childBirthDate = $this->input('form_data.tanggal_lahir_anak');
                $ageCategory = $this->input('form_data.kategori_usia_kia');

                if (! preg_match('/^\d{16}$/', $childNik)) {
                    $validator->errors()->add('form_data.nik_anak', 'NIK anak harus terdiri dari 16 digit.');
                }

                if ($this->validDate($childBirthDate)) {
                    $childAge = Carbon::parse($childBirthDate)->age;

                    if (Carbon::parse($childBirthDate)->isFuture() || $childAge >= 17) {
                        $validator->errors()->add('form_data.tanggal_lahir_anak', 'KIA hanya dapat diajukan untuk anak berusia kurang dari 17 tahun dan belum menikah.');
                    } elseif (($childAge < 5 && $ageCategory !== 'dibawah_5') || ($childAge >= 5 && $ageCategory !== 'usia_5_17')) {
                        $validator->errors()->add('form_data.kategori_usia_kia', 'Kategori usia harus sesuai dengan tanggal lahir anak.');
                    }
                }
            }

            if ($serviceCode === 'AKM' && ! preg_match('/^\d{16}$/', (string) $this->input('form_data.nik_almarhum'))) {
                $validator->errors()->add('form_data.nik_almarhum', 'NIK almarhum/almarhumah harus terdiri dari 16 digit.');
            }

            $eventDateField = match ($serviceCode) {
                'AKL' => 'tanggal_lahir_anak',
                'AKM' => 'tanggal_kematian',
                default => null,
            };

            if ($eventDateField && $this->validDate($this->input('form_data.'.$eventDateField)) && Carbon::parse($this->input('form_data.'.$eventDateField))->isFuture()) {
                $validator->errors()->add('form_data.'.$eventDateField, 'Tanggal peristiwa tidak boleh melewati hari ini.');
            }
        }];
    }

    private function validDate(mixed $value): bool
    {
        return is_string($value) && strtotime($value) !== false;
    }
}
