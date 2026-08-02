<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('letter_services')) {
            return;
        }

        $services = $this->services();
        $codes = array_column($services, 'code');

        DB::table('letter_services')
            ->whereNotIn('code', array_merge(['SKTM', 'KTP'], $codes))
            ->where('display_order', '>=', 2)
            ->increment('display_order', count($services));

        foreach ($services as $offset => $service) {
            DB::table('letter_services')->updateOrInsert(
                ['code' => $service['code']],
                [
                    'name' => $service['name'],
                    'slug' => Str::slug($service['name']),
                    'description' => $service['description'],
                    'icon' => $service['icon'],
                    'requirements_json' => json_encode($service['requirements'], JSON_UNESCAPED_UNICODE),
                    'form_schema_json' => json_encode($service['fields'], JSON_UNESCAPED_UNICODE),
                    'processing_days' => 3,
                    'fee_information' => 'Gratis',
                    'pickup_instructions' => null,
                    'is_active' => true,
                    'display_order' => $offset + 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ],
            );
        }

        DB::table('letter_services')->where('code', 'KTP')->update([
            'pickup_instructions' => null,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('letter_services')) {
            return;
        }

        $codes = array_column($this->services(), 'code');
        $serviceIdsWithApplications = Schema::hasTable('letter_applications')
            ? DB::table('letter_applications')
                ->whereIn('letter_service_id', DB::table('letter_services')->whereIn('code', $codes)->select('id'))
                ->pluck('letter_service_id')
                ->all()
            : [];

        DB::table('letter_services')
            ->whereIn('code', $codes)
            ->whereNotIn('id', $serviceIdsWithApplications)
            ->delete();

        DB::table('letter_services')
            ->whereNotIn('code', array_merge(['SKTM', 'KTP'], $codes))
            ->where('display_order', '>=', 2 + count($codes))
            ->decrement('display_order', count($codes));
    }

    private function services(): array
    {
        return [
            [
                'code' => 'SIPP',
                'name' => 'Surat Izin Survei/Penelitian',
                'icon' => 'fa-search',
                'description' => 'Permohonan surat izin atau pengantar survei dan penelitian di wilayah Desa Sukomulyo.',
                'requirements' => [
                    $this->requirement('surat-pengantar-instansi', 'Surat Pengantar Kampus/Instansi', 'Surat resmi yang memuat tujuan, lokasi, dan waktu kegiatan.'),
                    $this->requirement('identitas-pemohon', 'KTP atau Kartu Mahasiswa', 'Unggah identitas pemohon yang masih berlaku.'),
                    $this->requirement('proposal-penelitian', 'Proposal Survei/Penelitian', 'Unggah proposal yang menjelaskan kegiatan dan kebutuhan data.'),
                ],
                'fields' => [
                    $this->field('instansi_asal', 'Asal kampus/instansi', 'text', 150),
                    $this->field('nomor_surat_pengantar', 'Nomor surat pengantar', 'text', 100),
                    $this->field('judul_penelitian', 'Judul survei/penelitian', 'textarea', 500),
                    $this->field('lokasi_penelitian', 'Lokasi atau sasaran kegiatan', 'text', 200),
                    $this->field('tanggal_mulai', 'Tanggal mulai', 'date'),
                    $this->field('tanggal_selesai', 'Tanggal selesai', 'date'),
                ],
            ],
            [
                'code' => 'KK',
                'name' => 'Permohonan Kartu Keluarga/Pecah KK',
                'icon' => 'fa-users',
                'description' => 'Fasilitasi permohonan Kartu Keluarga baru atau pemisahan anggota menjadi Kartu Keluarga tersendiri.',
                'requirements' => [
                    $this->requirement('kartu-keluarga-lama', 'Kartu Keluarga Lama', 'Unggah KK asal yang jelas dan terbaca.'),
                    $this->requirement('ktp-pemohon', 'KTP-el Pemohon', 'Unggah KTP-el pemohon.'),
                    $this->requirement('dokumen-pendukung-kk', 'Dokumen Pendukung Pembentukan/Pecah KK', 'Contoh: buku nikah, akta perkawinan, akta perceraian, atau dokumen pendukung lain sesuai alasan pengajuan.'),
                ],
                'fields' => [
                    $this->selectField('jenis_pengajuan_kk', 'Jenis pengajuan Kartu Keluarga', ['kk_baru' => 'Kartu Keluarga baru', 'pecah_kk' => 'Pecah Kartu Keluarga']),
                    $this->field('nama_kepala_keluarga_baru', 'Nama kepala keluarga yang diajukan', 'text', 150),
                    $this->field('alasan_pengajuan_kk', 'Alasan pengajuan', 'textarea', 1000),
                ],
            ],
            [
                'code' => 'AKM',
                'name' => 'Permohonan Akta Kematian',
                'icon' => 'fa-certificate',
                'description' => 'Fasilitasi pencatatan peristiwa kematian dan permohonan penerbitan Akta Kematian.',
                'requirements' => [
                    $this->requirement('surat-keterangan-kematian', 'Surat Keterangan Kematian', 'Diterbitkan rumah sakit, fasilitas kesehatan, atau pihak yang berwenang.'),
                    $this->requirement('kk-almarhum', 'Kartu Keluarga Almarhum/Almarhumah', 'Unggah KK terakhir almarhum/almarhumah.'),
                    $this->requirement('ktp-almarhum', 'KTP-el Almarhum/Almarhumah', 'Unggah KTP-el terakhir jika tersedia.'),
                    $this->requirement('ktp-pelapor', 'KTP-el Pelapor', 'Unggah identitas pelapor peristiwa kematian.'),
                ],
                'fields' => [
                    $this->field('nama_almarhum', 'Nama almarhum/almarhumah', 'text', 150),
                    $this->field('nik_almarhum', 'NIK almarhum/almarhumah', 'text', 16),
                    $this->field('tanggal_kematian', 'Tanggal kematian', 'date'),
                    $this->field('tempat_kematian', 'Tempat kematian', 'text', 200),
                    $this->field('penyebab_kematian', 'Penyebab kematian', 'text', 200),
                    $this->field('hubungan_pelapor', 'Hubungan pelapor dengan almarhum/almarhumah', 'text', 100),
                ],
            ],
            [
                'code' => 'AKL',
                'name' => 'Permohonan Akta Kelahiran',
                'icon' => 'fa-birthday-cake',
                'description' => 'Fasilitasi pencatatan kelahiran dan permohonan penerbitan Akta Kelahiran.',
                'requirements' => [
                    $this->requirement('surat-keterangan-kelahiran', 'Surat Keterangan Kelahiran', 'Diterbitkan dokter, bidan, rumah sakit, atau pihak yang berwenang.'),
                    $this->requirement('kartu-keluarga-orang-tua', 'Kartu Keluarga Orang Tua', 'Unggah KK orang tua anak.'),
                    $this->requirement('buku-nikah-orang-tua', 'Buku Nikah/Akta Perkawinan Orang Tua', 'Unggah dokumen perkawinan orang tua.'),
                    $this->requirement('ktp-orang-tua', 'KTP-el Orang Tua', 'Unggah identitas kedua orang tua.'),
                ],
                'fields' => [
                    $this->field('nama_anak', 'Nama lengkap anak', 'text', 150),
                    $this->field('tempat_lahir_anak', 'Tempat lahir anak', 'text', 150),
                    $this->field('tanggal_lahir_anak', 'Tanggal lahir anak', 'date'),
                    $this->selectField('jenis_kelamin_anak', 'Jenis kelamin anak', ['L' => 'Laki-laki', 'P' => 'Perempuan']),
                    $this->field('nama_ayah', 'Nama ayah', 'text', 150),
                    $this->field('nama_ibu', 'Nama ibu', 'text', 150),
                    $this->field('anak_ke', 'Anak ke-', 'number', null, 1),
                ],
            ],
            [
                'code' => 'PPG',
                'name' => 'Permohonan Pindah Pergi',
                'icon' => 'fa-exchange-alt',
                'description' => 'Fasilitasi permohonan Surat Keterangan Pindah WNI untuk warga yang pindah keluar.',
                'requirements' => [
                    $this->requirement('kartu-keluarga', 'Kartu Keluarga', 'Unggah KK keluarga yang akan pindah.'),
                    $this->requirement('ktp-pemohon', 'KTP-el Pemohon', 'Unggah KTP-el pemohon.'),
                ],
                'fields' => [
                    $this->selectField('cakupan_pindah', 'Anggota keluarga yang pindah', ['seluruh_keluarga' => 'Seluruh keluarga', 'sebagian_anggota' => 'Sebagian anggota keluarga']),
                    $this->field('jumlah_anggota_pindah', 'Jumlah anggota yang pindah', 'number', null, 1),
                    $this->field('alasan_pindah', 'Alasan pindah', 'text', 200),
                    $this->field('alamat_tujuan', 'Alamat lengkap tujuan', 'textarea', 1000),
                    $this->field('desa_tujuan', 'Desa/kelurahan tujuan', 'text', 150),
                    $this->field('kecamatan_tujuan', 'Kecamatan tujuan', 'text', 150),
                    $this->field('kabupaten_tujuan', 'Kabupaten/kota tujuan', 'text', 150),
                    $this->field('provinsi_tujuan', 'Provinsi tujuan', 'text', 150),
                ],
            ],
            [
                'code' => 'PMI',
                'name' => 'Permohonan Pindah Masuk',
                'icon' => 'fa-home',
                'description' => 'Fasilitasi permohonan pindah masuk dan pembaruan data kependudukan di Desa Sukomulyo.',
                'requirements' => [
                    $this->requirement('surat-keterangan-pindah', 'Surat Keterangan Pindah WNI', 'Unggah surat pindah yang diterbitkan Disdukcapil daerah asal.'),
                    $this->requirement('kartu-keluarga-asal', 'Kartu Keluarga Daerah Asal', 'Unggah KK sebelum perpindahan.'),
                    $this->requirement('ktp-pemohon', 'KTP-el Pemohon', 'Unggah KTP-el pemohon.'),
                ],
                'fields' => [
                    $this->field('alamat_asal', 'Alamat lengkap daerah asal', 'textarea', 1000),
                    $this->field('nomor_surat_pindah', 'Nomor Surat Keterangan Pindah', 'text', 100),
                    $this->field('jumlah_anggota_pindah', 'Jumlah anggota yang pindah', 'number', null, 1),
                    $this->field('alamat_tujuan_sukomulyo', 'Alamat tujuan di Desa Sukomulyo', 'textarea', 1000),
                    $this->field('nama_kepala_keluarga_tujuan', 'Nama kepala keluarga tujuan', 'text', 150),
                ],
            ],
            [
                'code' => 'KIA',
                'name' => 'Permohonan Kartu Identitas Anak (KIA)',
                'icon' => 'fa-child',
                'description' => 'Fasilitasi permohonan KIA bagi anak berusia kurang dari 17 tahun dan belum menikah.',
                'requirements' => [
                    $this->requirement('akta-kelahiran-anak', 'Akta Kelahiran Anak', 'Unggah kutipan akta kelahiran anak.'),
                    $this->requirement('kartu-keluarga', 'Kartu Keluarga', 'Unggah KK yang memuat data anak.'),
                    $this->requirement('ktp-orang-tua', 'KTP-el Orang Tua/Wali', 'Unggah identitas orang tua atau wali.'),
                    [
                        ...$this->requirement('pas-foto-anak', 'Pas Foto Anak', 'Wajib untuk anak berusia 5 tahun sampai sebelum 17 tahun.'),
                        'required' => false,
                        'required_when' => ['field' => 'kategori_usia_kia', 'values' => ['usia_5_17']],
                    ],
                ],
                'fields' => [
                    $this->field('nama_anak', 'Nama lengkap anak', 'text', 150),
                    $this->field('nik_anak', 'NIK anak', 'text', 16),
                    $this->field('tempat_lahir_anak', 'Tempat lahir anak', 'text', 150),
                    $this->field('tanggal_lahir_anak', 'Tanggal lahir anak', 'date'),
                    $this->selectField('jenis_kelamin_anak', 'Jenis kelamin anak', ['L' => 'Laki-laki', 'P' => 'Perempuan']),
                    $this->selectField('kategori_usia_kia', 'Kategori usia anak', ['dibawah_5' => 'Di bawah 5 tahun', 'usia_5_17' => '5 tahun sampai sebelum 17 tahun']),
                    $this->field('nama_orang_tua_wali', 'Nama orang tua/wali', 'text', 150),
                ],
            ],
        ];
    }

    private function requirement(string $key, string $label, string $description): array
    {
        return compact('key', 'label', 'description') + ['required' => true];
    }

    private function field(string $key, string $label, string $type, ?int $max = null, ?int $min = null): array
    {
        return array_filter(compact('key', 'label', 'type', 'max', 'min'), fn (mixed $value): bool => $value !== null)
            + ['required' => true];
    }

    private function selectField(string $key, string $label, array $options): array
    {
        return compact('key', 'label', 'options') + ['type' => 'select', 'required' => true];
    }
};
