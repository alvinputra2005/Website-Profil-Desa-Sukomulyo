<?php

namespace App\Services\Statistics;

final class StatisticCategoryDetector
{
    public function detect(string $title, ?string $family = null): string
    {
        $title = mb_strtoupper($title);

        return match (true) {
            str_contains($title, 'BANTUAN SOSIAL'),
            str_contains($title, 'PERLINDUNGAN SOSIAL'),
            str_contains($title, 'DTKS') => 'perlindungan-sosial',

            str_contains($title, 'WANITA USIA SUBUR'),
            str_contains($title, 'PASANGAN USIA SUBUR'),
            str_contains($title, 'KONTRASEPSI'),
            str_contains($title, 'UMUR KAWIN'),
            str_contains($title, 'PESERTA KB') => 'keluarga-berencana',

            str_contains($title, 'PENDIDIKAN'),
            str_contains($title, 'SEKOLAH') => 'pendidikan',

            str_contains($title, 'PEKERJAAN'),
            str_contains($title, 'BEKERJA') => 'pekerjaan',

            str_contains($title, 'KESEHATAN'),
            str_contains($title, 'JAMINAN KESEHATAN'),
            str_contains($title, 'DISABILITAS') => 'kesehatan',

            str_contains($title, 'KELUARGA BERENCANA'),
            mb_strtoupper((string) $family) === 'IKB' => 'keluarga-berencana',

            str_contains($title, 'KELOMPOK UMUR'),
            str_contains($title, 'JENIS KELAMIN'),
            str_contains($title, 'KEPALA KELUARGA'),
            str_contains($title, 'PENDUDUK') => 'kependudukan',

            str_contains($title, 'KELUARGA') => 'keluarga',

            default => 'lainnya',
        };
    }
}
