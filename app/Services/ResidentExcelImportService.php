<?php

namespace App\Services;

use App\Models\FamilyCard;
use App\Models\PopulationArea;
use App\Models\Resident;
use App\Models\ResidentEvent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

final class ResidentExcelImportService
{
    private const HEADERS = [
        'nik', 'nomor_kk', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama',
        'status_perkawinan', 'kewarganegaraan', 'pendidikan', 'pekerjaan',
        'golongan_darah', 'alamat', 'dusun', 'rw', 'rt', 'status_penduduk',
        'status_data', 'tanggal_terdaftar', 'telepon', 'email', 'catatan',
    ];

    public static function headers(): array
    {
        return self::HEADERS;
    }

    public function import(UploadedFile $file): array
    {
        $sheet = IOFactory::load($file->getRealPath())->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);
        $headerRow = array_shift($rows) ?? [];
        $headers = array_map(fn ($value) => $this->normalizeHeader($value), $headerRow);
        $missing = array_diff(['nik', 'nama', 'jenis_kelamin'], $headers);

        if ($missing !== []) {
            return ['created' => 0, 'updated' => 0, 'failed' => 1, 'errors' => [
                'Kolom wajib tidak ditemukan: '.implode(', ', $missing).'. Gunakan template yang tersedia.',
            ]];
        }

        $result = ['created' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []];

        foreach ($rows as $index => $values) {
            $rowNumber = $index + 2;
            $row = [];
            foreach ($headers as $column => $header) {
                if ($header !== '') {
                    $row[$header] = $values[$column] ?? null;
                }
            }
            if (collect($row)->filter(fn ($value) => trim((string) $value) !== '')->isEmpty()) {
                continue;
            }

            try {
                $data = $this->validateAndNormalize($row);
                DB::transaction(function () use ($data, &$result) {
                    $resident = Resident::withTrashed()->where('nik', $data['nik'])->first();
                    $created = ! $resident;
                    $area = $this->resolveArea($data);
                    $family = $this->resolveFamily($data, $area);
                    $attributes = collect($data)->only([
                        'nik', 'name', 'sex', 'birth_place', 'birth_date', 'religion',
                        'marital_status', 'citizenship', 'education', 'occupation',
                        'blood_type', 'phone', 'email', 'current_address',
                        'resident_status', 'status', 'registered_at', 'notes',
                    ])->put('area_id', $area?->id)->all();
                    if ($data['family_card_number_provided']) {
                        $attributes['family_id'] = $family?->id;
                    }

                    if ($resident) {
                        $resident->restore();
                        $resident->update($attributes);
                        $result['updated']++;
                    } else {
                        $resident = Resident::create($attributes);
                        ResidentEvent::create([
                            'resident_id' => $resident->id,
                            'event_type' => 'arrival',
                            'event_date' => $resident->registered_at?->toDateString() ?? now()->toDateString(),
                            'reported_at' => now()->toDateString(),
                            'resident_name' => $resident->name,
                            'nik' => $resident->nik,
                            'sex' => $resident->sex,
                            'family_card_number' => $data['family_card_number'],
                            'origin_address' => null,
                            'notes' => 'Dibuat melalui impor Excel.',
                            'recorded_by' => auth()->id(),
                        ]);
                        $result['created']++;
                    }
                });
            } catch (Throwable $exception) {
                $result['failed']++;
                if (count($result['errors']) < 20) {
                    $message = method_exists($exception, 'errors')
                        ? collect($exception->errors())->flatten()->implode(' ')
                        : $exception->getMessage();
                    $result['errors'][] = "Baris {$rowNumber}: {$message}";
                }
            }
        }

        return $result;
    }

    private function validateAndNormalize(array $row): array
    {
        $data = [
            'nik' => $this->digits($row['nik'] ?? null),
            'family_card_number' => $this->digits($row['nomor_kk'] ?? null),
            'family_card_number_provided' => array_key_exists('nomor_kk', $row),
            'name' => trim((string) ($row['nama'] ?? '')),
            'sex' => $this->sex($row['jenis_kelamin'] ?? null),
            'birth_place' => $this->nullable($row['tempat_lahir'] ?? null),
            'birth_date' => $this->date($row['tanggal_lahir'] ?? null),
            'religion' => $this->nullable($row['agama'] ?? null),
            'marital_status' => $this->nullable($row['status_perkawinan'] ?? null),
            'citizenship' => strtoupper($this->nullable($row['kewarganegaraan'] ?? null) ?? 'WNI'),
            'education' => $this->nullable($row['pendidikan'] ?? null),
            'occupation' => $this->nullable($row['pekerjaan'] ?? null),
            'blood_type' => strtoupper($this->nullable($row['golongan_darah'] ?? null) ?? '-'),
            'current_address' => $this->nullable($row['alamat'] ?? null),
            'hamlet' => $this->nullable($row['dusun'] ?? null),
            'rw' => $this->digits($row['rw'] ?? null),
            'rt' => $this->digits($row['rt'] ?? null),
            'resident_status' => $this->residentStatus($row['status_penduduk'] ?? null),
            'status' => $this->dataStatus($row['status_data'] ?? null),
            'registered_at' => $this->date($row['tanggal_terdaftar'] ?? null),
            'phone' => $this->nullable($row['telepon'] ?? null),
            'email' => $this->nullable($row['email'] ?? null),
            'notes' => $this->nullable($row['catatan'] ?? null),
        ];

        return Validator::make($data, [
            'nik' => ['required', 'digits:16'],
            'family_card_number' => ['nullable', 'digits:16'],
            'family_card_number_provided' => ['required', 'boolean'],
            'name' => ['required', 'string', 'max:100'],
            'sex' => ['required', Rule::in(['L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'religion' => ['nullable', 'string', 'max:30'],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'citizenship' => ['required', Rule::in(['WNI', 'WNA'])],
            'education' => ['nullable', 'string', 'max:100'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'blood_type' => ['nullable', Rule::in(['A', 'B', 'AB', 'O', '-'])],
            'current_address' => ['nullable', 'string', 'max:255'],
            'hamlet' => ['nullable', 'string', 'max:100'],
            'rw' => ['nullable', 'digits_between:1,3'],
            'rt' => ['nullable', 'digits_between:1,3'],
            'resident_status' => ['required', Rule::in(['permanent', 'non_permanent'])],
            'status' => ['required', Rule::in(['active', 'moved', 'deceased', 'missing'])],
            'registered_at' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'nik' => 'NIK', 'family_card_number' => 'nomor KK',
            'name' => 'nama', 'sex' => 'jenis kelamin',
            'birth_date' => 'tanggal lahir', 'registered_at' => 'tanggal terdaftar',
        ])->validate();
    }

    private function resolveArea(array $data): ?PopulationArea
    {
        if (! $data['hamlet']) {
            return null;
        }

        return PopulationArea::firstOrCreate([
            'hamlet' => $data['hamlet'],
            'rw' => str_pad($data['rw'] ?? '', 2, '0', STR_PAD_LEFT),
            'rt' => str_pad($data['rt'] ?? '', 2, '0', STR_PAD_LEFT),
        ]);
    }

    private function resolveFamily(array $data, ?PopulationArea $area): ?FamilyCard
    {
        if (! $data['family_card_number']) {
            return null;
        }

        $family = FamilyCard::withTrashed()
            ->firstOrNew(['family_card_number' => $data['family_card_number']]);

        if ($family->trashed()) {
            $family->restore();
        }

        $family->area_id ??= $area?->id;
        $family->address ??= $data['current_address'];
        $family->registered_at ??= $data['registered_at'];
        $family->is_active = true;
        $family->save();

        return $family;
    }

    private function normalizeHeader(mixed $value): string
    {
        return Str::of((string) $value)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString();
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function digits(mixed $value): ?string
    {
        $value = preg_replace('/\D/', '', trim((string) $value));

        return $value === '' ? null : $value;
    }

    private function date(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        return Carbon::parse(str_replace('/', '-', trim((string) $value)))->format('Y-m-d');
    }

    private function sex(mixed $value): string
    {
        $value = Str::lower(trim((string) $value));

        return in_array($value, ['l', 'laki-laki', 'laki laki', 'male'], true) ? 'L'
            : (in_array($value, ['p', 'perempuan', 'female'], true) ? 'P' : strtoupper($value));
    }

    private function residentStatus(mixed $value): string
    {
        $value = Str::lower(trim((string) $value));

        return in_array($value, ['', 'tetap', 'permanent'], true) ? 'permanent' : (in_array($value, ['tidak tetap', 'non permanent', 'non_permanent'], true) ? 'non_permanent' : $value);
    }

    private function dataStatus(mixed $value): string
    {
        return match (Str::lower(trim((string) $value))) {
            '', 'aktif', 'active' => 'active',
            'pindah', 'moved' => 'moved',
            'meninggal', 'deceased' => 'deceased',
            'hilang', 'missing' => 'missing',
            default => Str::lower(trim((string) $value)),
        };
    }
}
