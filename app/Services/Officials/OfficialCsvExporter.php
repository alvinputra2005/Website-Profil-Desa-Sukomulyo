<?php

namespace App\Services\Officials;

use App\Models\Official;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OfficialCsvExporter
{
    public function download(): StreamedResponse
    {
        $filename = 'buku-pemerintah-desa-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'No', 'Nama', 'NIK', 'NIPD', 'NIP', 'Tag ID Card', 'Tempat Lahir', 'Tanggal Lahir',
                'Jenis Kelamin', 'Agama', 'Pendidikan', 'Pangkat/Golongan', 'Jabatan',
                'Nomor SK Pengangkatan', 'Tanggal SK Pengangkatan', 'Nomor SK Pemberhentian',
                'Tanggal SK Pemberhentian', 'Masa Jabatan', 'Status',
            ], ';');
            $number = 0;
            Official::orderBy('display_order')->orderBy('name')
                ->each(function (Official $official) use ($handle, &$number): void {
                    $number++;
                    fputcsv($handle, [
                        $number,
                        $official->full_name,
                        $official->nik,
                        $official->village_employee_number,
                        $official->nip,
                        $official->id_card_tag,
                        $official->birth_place,
                        $official->birth_date?->format('Y-m-d'),
                        $official->sex_label,
                        $official->religion,
                        $official->education,
                        $official->rank_grade,
                        $official->position_label,
                        $official->appointment_decree,
                        $official->appointment_date?->format('Y-m-d'),
                        $official->dismissal_decree,
                        $official->dismissal_date?->format('Y-m-d'),
                        $official->term,
                        $official->is_active ? 'Aktif' : 'Tidak Aktif',
                    ], ';');
                });
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
