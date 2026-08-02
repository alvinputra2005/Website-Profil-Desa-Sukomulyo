<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('letter_services')) {
            return;
        }

        DB::table('letter_services')
            ->whereNotIn('code', ['SKTM', 'KTP'])
            ->where('display_order', '>=', 1)
            ->increment('display_order');

        $now = now();
        DB::table('letter_services')->updateOrInsert(
            ['code' => 'KTP'],
            [
                'name' => 'Pengajuan KTP-el',
                'slug' => 'pengajuan-ktp-el',
                'description' => 'Fasilitasi pengajuan KTP-el baru, hilang, rusak, perubahan data, atau pindah datang. Penerbitan dilakukan oleh Disdukcapil Kabupaten Malang.',
                'icon' => 'fa-id-card',
                'requirements_json' => json_encode([
                    ['key' => 'kartu-keluarga', 'label' => 'Kartu Keluarga', 'description' => 'Unggah KK yang jelas dan terbaca.', 'required' => true],
                    ['key' => 'surat-kehilangan', 'label' => 'Surat Kehilangan dari Kepolisian', 'description' => 'Wajib untuk penggantian KTP-el yang hilang.', 'required' => false, 'required_for' => ['hilang']],
                    ['key' => 'ktp-lama-rusak', 'label' => 'KTP-el Lama atau Rusak', 'description' => 'Wajib untuk KTP-el rusak atau perubahan data.', 'required' => false, 'required_for' => ['rusak', 'perubahan_data']],
                    ['key' => 'bukti-perubahan-data', 'label' => 'Bukti Pendukung Perubahan Data', 'description' => 'Wajib untuk perubahan data KTP-el.', 'required' => false, 'required_for' => ['perubahan_data']],
                    ['key' => 'surat-pindah', 'label' => 'Surat Keterangan Pindah', 'description' => 'Wajib untuk pengajuan karena pindah datang.', 'required' => false, 'required_for' => ['pindah_datang']],
                ], JSON_UNESCAPED_UNICODE),
                'form_schema_json' => json_encode([
                    [
                        'key' => 'jenis_pengajuan_ktp',
                        'label' => 'Jenis pengajuan KTP-el',
                        'type' => 'select',
                        'options' => [
                            'baru' => 'Baru / pemula',
                            'hilang' => 'Penggantian karena hilang',
                            'rusak' => 'Penggantian karena rusak',
                            'perubahan_data' => 'Perubahan data',
                            'pindah_datang' => 'Pindah datang',
                        ],
                        'required' => true,
                    ],
                    [
                        'key' => 'status_perkawinan',
                        'label' => 'Status perkawinan',
                        'type' => 'select',
                        'options' => [
                            'belum_kawin' => 'Belum kawin',
                            'kawin' => 'Kawin',
                            'cerai_hidup' => 'Cerai hidup',
                            'cerai_mati' => 'Cerai mati',
                        ],
                        'required' => true,
                    ],
                ], JSON_UNESCAPED_UNICODE),
                'processing_days' => 3,
                'fee_information' => 'Gratis',
                'pickup_instructions' => 'Pemohon baru yang belum pernah merekam wajib hadir untuk perekaman biometrik sesuai arahan Disdukcapil.',
                'is_active' => true,
                'display_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('letter_services')) {
            return;
        }

        $serviceId = DB::table('letter_services')->where('code', 'KTP')->value('id');
        $hasApplications = $serviceId && Schema::hasTable('letter_applications')
            && DB::table('letter_applications')->where('letter_service_id', $serviceId)->exists();

        if ($serviceId && ! $hasApplications) {
            DB::table('letter_services')->where('id', $serviceId)->delete();
        }

        DB::table('letter_services')
            ->whereNotIn('code', ['SKTM', 'KTP'])
            ->where('display_order', '>', 1)
            ->decrement('display_order');
    }
};
