<?php

namespace Database\Seeders;

use App\Models\LetterService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LetterServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Persyaratan awal ini wajib diverifikasi kembali oleh perangkat Desa Sukomulyo.
        $services = [
            ['SKU', 'Surat Keterangan Usaha', ['KTP asli', 'Kartu Keluarga', 'Data usaha']],
            ['SKD', 'Surat Keterangan Domisili', ['KTP asli', 'Kartu Keluarga']],
            ['SKTM', 'Surat Keterangan Tidak Mampu', ['KTP asli', 'Kartu Keluarga']],
            ['SKCK', 'Surat Pengantar SKCK', ['KTP asli', 'Kartu Keluarga', 'Pas foto sesuai ketentuan']],
            ['SKBM', 'Surat Keterangan Belum Menikah', ['KTP asli', 'Kartu Keluarga']],
            ['SKL', 'Surat Keterangan Kelahiran', ['Kartu Keluarga', 'Keterangan kelahiran']],
            ['SKM', 'Surat Keterangan Kematian', ['Kartu Keluarga', 'Keterangan kematian']],
        ];
        foreach ($services as $order => [$code, $name, $requirements]) {
            LetterService::updateOrCreate(['code' => $code], [
                'name' => $name, 'slug' => Str::slug($name),
                'description' => "Layanan permohonan awal {$name}. Surat fisik diambil di kantor desa setelah dinyatakan siap.",
                'requirements_json' => collect($requirements)->map(fn ($label, $index) => ['key' => 'requirement_'.($index + 1), 'label' => $label, 'description' => 'Dibawa saat pengambilan atau sesuai arahan petugas.', 'required' => true])->all(),
                'processing_days' => 3, 'fee_information' => 'Gratis', 'is_active' => true, 'display_order' => $order,
            ]);
        }
    }
}
