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
            ['SKTM', 'Surat Keterangan', 'fa-heart', ['KTP asli', 'Kartu Keluarga']],
            [
                'KTP',
                'Pengajuan KTP-el',
                'fa-id-card',
                [
                    ['key' => 'kartu-keluarga', 'label' => 'Kartu Keluarga', 'description' => 'Unggah KK yang jelas dan terbaca.', 'required' => true],
                    ['key' => 'surat-kehilangan', 'label' => 'Surat Kehilangan dari Kepolisian', 'description' => 'Wajib untuk penggantian KTP-el yang hilang.', 'required' => false, 'required_for' => ['hilang']],
                    ['key' => 'ktp-lama-rusak', 'label' => 'KTP-el Lama atau Rusak', 'description' => 'Wajib untuk KTP-el rusak atau perubahan data.', 'required' => false, 'required_for' => ['rusak', 'perubahan_data']],
                    ['key' => 'bukti-perubahan-data', 'label' => 'Bukti Pendukung Perubahan Data', 'description' => 'Wajib untuk perubahan data KTP-el.', 'required' => false, 'required_for' => ['perubahan_data']],
                    ['key' => 'surat-pindah', 'label' => 'Surat Keterangan Pindah', 'description' => 'Wajib untuk pengajuan karena pindah datang.', 'required' => false, 'required_for' => ['pindah_datang']],
                ],
                [
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
                ],
            ],
            [
                'SIPP',
                'Surat Izin Survei/Penelitian',
                'fa-search',
                [
                    ['key' => 'surat-pengantar-instansi', 'label' => 'Surat Pengantar Kampus/Instansi', 'description' => 'Surat resmi yang memuat tujuan, lokasi, dan waktu kegiatan.', 'required' => true],
                    ['key' => 'identitas-pemohon', 'label' => 'KTP atau Kartu Mahasiswa', 'description' => 'Unggah identitas pemohon yang masih berlaku.', 'required' => true],
                    ['key' => 'proposal-penelitian', 'label' => 'Proposal Survei/Penelitian', 'description' => 'Unggah proposal yang menjelaskan kegiatan dan kebutuhan data.', 'required' => true],
                ],
                [
                    ['key' => 'instansi_asal', 'label' => 'Asal kampus/instansi', 'type' => 'text', 'max' => 150, 'required' => true],
                    ['key' => 'nomor_surat_pengantar', 'label' => 'Nomor surat pengantar', 'type' => 'text', 'max' => 100, 'required' => true],
                    ['key' => 'judul_penelitian', 'label' => 'Judul survei/penelitian', 'type' => 'textarea', 'max' => 500, 'required' => true],
                    ['key' => 'lokasi_penelitian', 'label' => 'Lokasi atau sasaran kegiatan', 'type' => 'text', 'max' => 200, 'required' => true],
                    ['key' => 'tanggal_mulai', 'label' => 'Tanggal mulai', 'type' => 'date', 'required' => true],
                    ['key' => 'tanggal_selesai', 'label' => 'Tanggal selesai', 'type' => 'date', 'required' => true],
                ],
                'Permohonan surat izin atau pengantar survei dan penelitian di wilayah Desa Sukomulyo.',
            ],
            [
                'KK',
                'Permohonan Kartu Keluarga/Pecah KK',
                'fa-users',
                [
                    ['key' => 'kartu-keluarga-lama', 'label' => 'Kartu Keluarga Lama', 'description' => 'Unggah KK asal yang jelas dan terbaca.', 'required' => true],
                    ['key' => 'ktp-pemohon', 'label' => 'KTP-el Pemohon', 'description' => 'Unggah KTP-el pemohon.', 'required' => true],
                    ['key' => 'dokumen-pendukung-kk', 'label' => 'Dokumen Pendukung Pembentukan/Pecah KK', 'description' => 'Contoh: buku nikah, akta perkawinan, akta perceraian, atau dokumen pendukung lain sesuai alasan pengajuan.', 'required' => true],
                ],
                [
                    ['key' => 'jenis_pengajuan_kk', 'label' => 'Jenis pengajuan Kartu Keluarga', 'type' => 'select', 'options' => ['kk_baru' => 'Kartu Keluarga baru', 'pecah_kk' => 'Pecah Kartu Keluarga'], 'required' => true],
                    ['key' => 'nama_kepala_keluarga_baru', 'label' => 'Nama kepala keluarga yang diajukan', 'type' => 'text', 'max' => 150, 'required' => true],
                    ['key' => 'alasan_pengajuan_kk', 'label' => 'Alasan pengajuan', 'type' => 'textarea', 'max' => 1000, 'required' => true],
                ],
                'Fasilitasi permohonan Kartu Keluarga baru atau pemisahan anggota menjadi Kartu Keluarga tersendiri.',
            ],
            [
                'AKM',
                'Permohonan Akta Kematian',
                'fa-certificate',
                [
                    ['key' => 'surat-keterangan-kematian', 'label' => 'Surat Keterangan Kematian', 'description' => 'Diterbitkan rumah sakit, fasilitas kesehatan, atau pihak yang berwenang.', 'required' => true],
                    ['key' => 'kk-almarhum', 'label' => 'Kartu Keluarga Almarhum/Almarhumah', 'description' => 'Unggah KK terakhir almarhum/almarhumah.', 'required' => true],
                    ['key' => 'ktp-almarhum', 'label' => 'KTP-el Almarhum/Almarhumah', 'description' => 'Unggah KTP-el terakhir jika tersedia.', 'required' => true],
                    ['key' => 'ktp-pelapor', 'label' => 'KTP-el Pelapor', 'description' => 'Unggah identitas pelapor peristiwa kematian.', 'required' => true],
                ],
                [
                    ['key' => 'nama_almarhum', 'label' => 'Nama almarhum/almarhumah', 'type' => 'text', 'max' => 150, 'required' => true],
                    ['key' => 'nik_almarhum', 'label' => 'NIK almarhum/almarhumah', 'type' => 'text', 'max' => 16, 'required' => true],
                    ['key' => 'tanggal_kematian', 'label' => 'Tanggal kematian', 'type' => 'date', 'required' => true],
                    ['key' => 'tempat_kematian', 'label' => 'Tempat kematian', 'type' => 'text', 'max' => 200, 'required' => true],
                    ['key' => 'penyebab_kematian', 'label' => 'Penyebab kematian', 'type' => 'text', 'max' => 200, 'required' => true],
                    ['key' => 'hubungan_pelapor', 'label' => 'Hubungan pelapor dengan almarhum/almarhumah', 'type' => 'text', 'max' => 100, 'required' => true],
                ],
                'Fasilitasi pencatatan peristiwa kematian dan permohonan penerbitan Akta Kematian.',
            ],
            [
                'AKL',
                'Permohonan Akta Kelahiran',
                'fa-birthday-cake',
                [
                    ['key' => 'surat-keterangan-kelahiran', 'label' => 'Surat Keterangan Kelahiran', 'description' => 'Diterbitkan dokter, bidan, rumah sakit, atau pihak yang berwenang.', 'required' => true],
                    ['key' => 'kartu-keluarga-orang-tua', 'label' => 'Kartu Keluarga Orang Tua', 'description' => 'Unggah KK orang tua anak.', 'required' => true],
                    ['key' => 'buku-nikah-orang-tua', 'label' => 'Buku Nikah/Akta Perkawinan Orang Tua', 'description' => 'Unggah dokumen perkawinan orang tua.', 'required' => true],
                    ['key' => 'ktp-orang-tua', 'label' => 'KTP-el Orang Tua', 'description' => 'Unggah identitas kedua orang tua.', 'required' => true],
                ],
                [
                    ['key' => 'nama_anak', 'label' => 'Nama lengkap anak', 'type' => 'text', 'max' => 150, 'required' => true],
                    ['key' => 'tempat_lahir_anak', 'label' => 'Tempat lahir anak', 'type' => 'text', 'max' => 150, 'required' => true],
                    ['key' => 'tanggal_lahir_anak', 'label' => 'Tanggal lahir anak', 'type' => 'date', 'required' => true],
                    ['key' => 'jenis_kelamin_anak', 'label' => 'Jenis kelamin anak', 'type' => 'select', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan'], 'required' => true],
                    ['key' => 'nama_ayah', 'label' => 'Nama ayah', 'type' => 'text', 'max' => 150, 'required' => true],
                    ['key' => 'nama_ibu', 'label' => 'Nama ibu', 'type' => 'text', 'max' => 150, 'required' => true],
                    ['key' => 'anak_ke', 'label' => 'Anak ke-', 'type' => 'number', 'min' => 1, 'required' => true],
                ],
                'Fasilitasi pencatatan kelahiran dan permohonan penerbitan Akta Kelahiran.',
            ],
            [
                'PPG',
                'Permohonan Pindah Pergi',
                'fa-exchange-alt',
                [
                    ['key' => 'kartu-keluarga', 'label' => 'Kartu Keluarga', 'description' => 'Unggah KK keluarga yang akan pindah.', 'required' => true],
                    ['key' => 'ktp-pemohon', 'label' => 'KTP-el Pemohon', 'description' => 'Unggah KTP-el pemohon.', 'required' => true],
                ],
                [
                    ['key' => 'cakupan_pindah', 'label' => 'Anggota keluarga yang pindah', 'type' => 'select', 'options' => ['seluruh_keluarga' => 'Seluruh keluarga', 'sebagian_anggota' => 'Sebagian anggota keluarga'], 'required' => true],
                    ['key' => 'jumlah_anggota_pindah', 'label' => 'Jumlah anggota yang pindah', 'type' => 'number', 'min' => 1, 'required' => true],
                    ['key' => 'alasan_pindah', 'label' => 'Alasan pindah', 'type' => 'text', 'max' => 200, 'required' => true],
                    ['key' => 'alamat_tujuan', 'label' => 'Alamat lengkap tujuan', 'type' => 'textarea', 'max' => 1000, 'required' => true],
                    ['key' => 'desa_tujuan', 'label' => 'Desa/kelurahan tujuan', 'type' => 'text', 'max' => 150, 'required' => true],
                    ['key' => 'kecamatan_tujuan', 'label' => 'Kecamatan tujuan', 'type' => 'text', 'max' => 150, 'required' => true],
                    ['key' => 'kabupaten_tujuan', 'label' => 'Kabupaten/kota tujuan', 'type' => 'text', 'max' => 150, 'required' => true],
                    ['key' => 'provinsi_tujuan', 'label' => 'Provinsi tujuan', 'type' => 'text', 'max' => 150, 'required' => true],
                ],
                'Fasilitasi permohonan Surat Keterangan Pindah WNI untuk warga yang pindah keluar.',
            ],
            [
                'PMI',
                'Permohonan Pindah Masuk',
                'fa-home',
                [
                    ['key' => 'surat-keterangan-pindah', 'label' => 'Surat Keterangan Pindah WNI', 'description' => 'Unggah surat pindah yang diterbitkan Disdukcapil daerah asal.', 'required' => true],
                    ['key' => 'kartu-keluarga-asal', 'label' => 'Kartu Keluarga Daerah Asal', 'description' => 'Unggah KK sebelum perpindahan.', 'required' => true],
                    ['key' => 'ktp-pemohon', 'label' => 'KTP-el Pemohon', 'description' => 'Unggah KTP-el pemohon.', 'required' => true],
                ],
                [
                    ['key' => 'alamat_asal', 'label' => 'Alamat lengkap daerah asal', 'type' => 'textarea', 'max' => 1000, 'required' => true],
                    ['key' => 'nomor_surat_pindah', 'label' => 'Nomor Surat Keterangan Pindah', 'type' => 'text', 'max' => 100, 'required' => true],
                    ['key' => 'jumlah_anggota_pindah', 'label' => 'Jumlah anggota yang pindah', 'type' => 'number', 'min' => 1, 'required' => true],
                    ['key' => 'alamat_tujuan_sukomulyo', 'label' => 'Alamat tujuan di Desa Sukomulyo', 'type' => 'textarea', 'max' => 1000, 'required' => true],
                    ['key' => 'nama_kepala_keluarga_tujuan', 'label' => 'Nama kepala keluarga tujuan', 'type' => 'text', 'max' => 150, 'required' => true],
                ],
                'Fasilitasi permohonan pindah masuk dan pembaruan data kependudukan di Desa Sukomulyo.',
            ],
            [
                'KIA',
                'Permohonan Kartu Identitas Anak (KIA)',
                'fa-child',
                [
                    ['key' => 'akta-kelahiran-anak', 'label' => 'Akta Kelahiran Anak', 'description' => 'Unggah kutipan akta kelahiran anak.', 'required' => true],
                    ['key' => 'kartu-keluarga', 'label' => 'Kartu Keluarga', 'description' => 'Unggah KK yang memuat data anak.', 'required' => true],
                    ['key' => 'ktp-orang-tua', 'label' => 'KTP-el Orang Tua/Wali', 'description' => 'Unggah identitas orang tua atau wali.', 'required' => true],
                    ['key' => 'pas-foto-anak', 'label' => 'Pas Foto Anak', 'description' => 'Wajib untuk anak berusia 5 tahun sampai sebelum 17 tahun.', 'required' => false, 'required_when' => ['field' => 'kategori_usia_kia', 'values' => ['usia_5_17']]],
                ],
                [
                    ['key' => 'nama_anak', 'label' => 'Nama lengkap anak', 'type' => 'text', 'max' => 150, 'required' => true],
                    ['key' => 'nik_anak', 'label' => 'NIK anak', 'type' => 'text', 'max' => 16, 'required' => true],
                    ['key' => 'tempat_lahir_anak', 'label' => 'Tempat lahir anak', 'type' => 'text', 'max' => 150, 'required' => true],
                    ['key' => 'tanggal_lahir_anak', 'label' => 'Tanggal lahir anak', 'type' => 'date', 'required' => true],
                    ['key' => 'jenis_kelamin_anak', 'label' => 'Jenis kelamin anak', 'type' => 'select', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan'], 'required' => true],
                    ['key' => 'kategori_usia_kia', 'label' => 'Kategori usia anak', 'type' => 'select', 'options' => ['dibawah_5' => 'Di bawah 5 tahun', 'usia_5_17' => '5 tahun sampai sebelum 17 tahun'], 'required' => true],
                    ['key' => 'nama_orang_tua_wali', 'label' => 'Nama orang tua/wali', 'type' => 'text', 'max' => 150, 'required' => true],
                ],
                'Fasilitasi permohonan KIA bagi anak berusia kurang dari 17 tahun dan belum menikah.',
            ],
            ['SKU', 'Surat Keterangan Usaha', 'fa-briefcase', ['KTP asli', 'Kartu Keluarga', 'Data usaha']],
            ['SKD', 'Surat Keterangan Domisili', 'fa-home', ['KTP asli', 'Kartu Keluarga']],
            ['SKCK', 'Surat Pengantar SKCK', 'fa-file', ['KTP asli', 'Kartu Keluarga', 'Pas foto sesuai ketentuan']],
            ['SKBM', 'Surat Keterangan Belum Menikah', 'fa-user', ['KTP asli', 'Kartu Keluarga']],
            ['SKL', 'Surat Keterangan Kelahiran', 'fa-birthday-cake', ['Kartu Keluarga', 'Keterangan kelahiran']],
            ['SKM', 'Surat Keterangan Kematian', 'fa-certificate', ['Kartu Keluarga', 'Keterangan kematian']],
        ];
        foreach ($services as $order => $service) {
            [$code, $name, $icon, $requirements] = $service;
            $formSchema = $service[4] ?? null;
            $description = $service[5] ?? null;
            $data = [
                'name' => $name, 'slug' => Str::slug($name),
                'description' => $description ?? ($code === 'KTP'
                    ? 'Fasilitasi pengajuan KTP-el baru, hilang, rusak, perubahan data, atau pindah datang. Penerbitan dilakukan oleh Disdukcapil Kabupaten Malang.'
                    : "Layanan permohonan awal {$name}. Surat fisik diambil di kantor desa setelah dinyatakan siap."),
                'icon' => $icon,
                'requirements_json' => collect($requirements)->map(fn ($requirement, $index) => is_array($requirement)
                    ? $requirement
                    : ['key' => 'requirement_'.($index + 1), 'label' => $requirement, 'description' => 'Dibawa saat pengambilan atau sesuai arahan petugas.', 'required' => true])->all(),
                'processing_days' => 3,
                'fee_information' => 'Gratis',
                'is_active' => true,
                'display_order' => $order,
            ];

            if ($formSchema !== null) {
                $data['form_schema_json'] = $formSchema;
                $data['pickup_instructions'] = null;
            } elseif ($code === 'KTP') {
                $data['pickup_instructions'] = null;
            }

            LetterService::updateOrCreate(['code' => $code], $data);
        }
    }
}
