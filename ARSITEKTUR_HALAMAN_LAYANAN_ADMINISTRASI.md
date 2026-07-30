# ARSITEKTUR HALAMAN LAYANAN ADMINISTRASI

**Proyek:** Website Profil Desa Sukomulyo  
**Repository:** `alvinputra2005/Website-Profil-Desa-Sukomulyo`  
**Branch target:** `feature/informasi-desa`  
**Halaman publik:** `/informasi-desa/layanan-administrasi`  
**Framework:** Laravel 12, PHP 8.2, Blade, Vite, CSS proyek, JavaScript ES Module  
**Status dokumen:** Spesifikasi layout, konten, interaksi, dan arsitektur implementasi  
**Tanggal rancangan:** 30 Juli 2026  

---

## 1. Tujuan

Membangun ulang halaman **Layanan Administrasi** agar masyarakat dapat:

1. Melihat seluruh jenis pelayanan administrasi desa.
2. Membuka persyaratan pelayanan melalui accordion/dropdown.
3. Membuka subjenis pelayanan melalui dropdown tingkat kedua.
4. Memahami alur pelayanan secara berurutan.
5. Memahami tata cara pengajuan secara ringkas.
6. Mengetahui jadwal pelayanan, waktu istirahat, nomor WhatsApp, dan informasi biaya.
7. Menggunakan halaman dengan nyaman pada desktop, tablet, maupun ponsel.
8. Mengakses seluruh fungsi menggunakan mouse, keyboard, dan pembaca layar.
9. Tetap menggunakan identitas visual hijau Website Desa Sukomulyo.
10. Tetap kompatibel dengan navigasi AJAX yang sudah digunakan proyek.

---

## 2. Keputusan Utama

| Bagian | Keputusan |
|---|---|
| Struktur halaman | Layout dua kolom seperti halaman Berita Desa |
| Kolom utama | Daftar persyaratan pelayanan berbentuk accordion bertingkat |
| Sidebar kanan | Accordion **Alur Pelayanan** dan **Tata Cara Pengajuan** |
| Informasi tambahan | Card ringkas jadwal, biaya, dan kontak WhatsApp |
| Jenis layanan | Mengikuti isi poster persyaratan yang diberikan |
| Accordion utama | Hanya satu layanan utama terbuka dalam satu waktu |
| Accordion bertingkat | Digunakan pada layanan yang memiliki subjenis, terutama Kartu Keluarga |
| Data konten | Dipisahkan dari Blade; jangan menulis semua data langsung di view |
| Rendering | Server-side rendering dengan Blade |
| JavaScript | Memanfaatkan pola toggle yang sudah ada pada `resources/js/app.js` |
| Library baru | Tidak diperlukan |
| Warna | Mengikuti variabel warna website, terutama hijau `#526b42` |
| Mobile | Sidebar turun ke bawah konten utama |
| Status biaya | Ditampilkan jelas sebagai **GRATIS / tanpa dipungut biaya** |
| Kontak | WhatsApp `085731625435` |
| Waktu proses Dukcapil | Ditampilkan sebagai estimasi `1 × 24 jam` sesuai poster |

---

## 3. Kondisi Repository Saat Ini

Pada branch `feature/informasi-desa`, halaman publik sudah memiliki route:

```php
Route::get('/informasi-desa/{section}', [PublicationController::class, 'show'])
    ->where('section', 'layanan-administrasi|agenda|bantuan-sosial|informasi-publik')
    ->name('informasi-desa.detail');
```

Route tersebut memanggil:

```text
app/Http/Controllers/Web/PublicationController.php
└── show(PublicSiteService $site, string $section)
```

Kemudian data halaman disiapkan oleh:

```text
app/Services/Web/PublicSiteService.php
└── informationDetail(string $section)
```

Kondisi halaman **Layanan Administrasi** saat ini:

- masih menggunakan data fallback sederhana;
- hanya menampilkan tiga panel umum;
- belum mempunyai daftar persyaratan per layanan;
- belum mempunyai accordion bertingkat;
- belum mempunyai sidebar alur pelayanan;
- belum mempunyai card jadwal dan kontak;
- masih memakai view generik:

```text
resources/views/pages/information-detail.blade.php
```

View generik tersebut juga digunakan oleh halaman informasi lainnya. Karena itu, halaman Layanan Administrasi sebaiknya memperoleh view khusus agar perubahan layout tidak merusak:

- Agenda Desa;
- Bantuan Sosial;
- Informasi Publik.

---

## 4. Prinsip Implementasi

### 4.1 Jangan mengubah halaman informasi lain

Jangan mengganti total `information-detail.blade.php` untuk seluruh halaman.

Gunakan view khusus:

```text
resources/views/pages/administrative-services.blade.php
```

Pada `PublicSiteService::informationDetail()`, lakukan pemisahan khusus saat:

```php
$section === 'layanan-administrasi'
```

### 4.2 Jangan menulis seluruh konten di Blade

Data persyaratan harus berada pada satu sumber data terstruktur agar:

- mudah diperiksa;
- mudah diperbarui;
- tidak membuat view sangat panjang;
- dapat digunakan ulang untuk halaman admin pada pengembangan berikutnya;
- dapat diuji tanpa mem-parsing HTML.

Untuk tahap saat ini, gunakan:

```text
config/administrative_services.php
```

Alternatif yang tetap diterima:

```text
app/Support/AdministrativeServiceCatalog.php
```

Jangan membuat migration atau tabel baru hanya untuk konten statis ini apabila belum ada kebutuhan pengelolaan melalui CMS.

### 4.3 Gunakan JavaScript yang sudah ada

`resources/js/app.js` sudah mempunyai pola:

```html
data-sidebar-toggle
data-sidebar-accordion
data-sidebar-accordion-toggle
data-sidebar-panel
```

Pola tersebut dapat digunakan kembali untuk:

- accordion persyaratan utama;
- accordion tingkat kedua;
- accordion sidebar.

Pastikan binding tetap aman ketika halaman dimuat ulang melalui navigasi AJAX.

---

## 5. Struktur Halaman Final

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│ PAGE HEADER                                                                 │
│ Layanan Administrasi                                                        │
│ Persyaratan, alur pelayanan, jadwal, dan tata cara pengajuan.              │
│ Breadcrumb: Informasi Desa / Layanan Administrasi                           │
└─────────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────┬──────────────────────────────┐
│ KOLOM UTAMA                                  │ SIDEBAR KANAN                │
│                                              │                              │
│ Judul: Persyaratan Pelayanan                 │ [Dropdown] Alur Pelayanan    │
│ Deskripsi singkat                            │                              │
│                                              │ [Dropdown] Tata Cara         │
│ [Cari jenis pelayanan...]                    │ Pengajuan                    │
│                                              │                              │
│ [▼] Pengajuan Kartu Keluarga                 │ ┌──────────────────────────┐ │
│     [▼] Penerbitan KK Baru                   │ │ INFORMASI PELAYANAN      │ │
│          • Persyaratan                       │ │ Senin–Jumat              │ │
│     [▼] Perubahan Data                       │ │ 08.00–16.00 WIB          │ │
│     [▼] Hilang/Rusak                         │ │ Istirahat 12.00–13.00    │ │
│                                              │ │ Sabtu/Minggu/libur       │ │
│ [▼] Pengajuan Akta Kematian                  │ │ WhatsApp                  │ │
│ [▼] Administrasi Nikah                       │ │ 085731625435             │ │
│ [▼] SKCK                                     │ │ GRATIS                    │ │
│ [▼] Pengajuan Akta Kelahiran                 │ └──────────────────────────┘ │
│ [▼] Pindah Tempat                            │                              │
│ [▼] Kartu Identitas Anak                     │                              │
│ [▼] BPJS                                     │                              │
└──────────────────────────────────────────────┴──────────────────────────────┘
```

---

## 6. Hierarki Accordion Persyaratan

### 6.1 Tingkat pertama

Accordion tingkat pertama menampilkan delapan jenis pelayanan:

1. Pengajuan Kartu Keluarga.
2. Pengajuan Akta Kematian.
3. Administrasi Nikah.
4. SKCK.
5. Pengajuan Akta Kelahiran.
6. Pindah Tempat (Masuk/Keluar).
7. Kartu Identitas Anak (KIA).
8. BPJS.

### 6.2 Tingkat kedua

**Pengajuan Kartu Keluarga** mempunyai tiga subjenis:

1. Penerbitan KK Baru.
2. Penerbitan KK Perubahan Data.
3. Penerbitan KK Hilang/Rusak.

Layanan lain langsung menampilkan daftar persyaratan setelah accordion utama dibuka.

### 6.3 Aturan buka-tutup

- Pada kondisi awal, accordion pertama boleh terbuka.
- Hanya satu accordion tingkat pertama terbuka dalam satu waktu.
- Accordion tingkat kedua hanya menutup accordion tingkat kedua lain yang masih berada pada layanan induk yang sama.
- Membuka tingkat kedua tidak boleh menutup induknya.
- Menutup induk harus menyembunyikan seluruh isi tingkat kedua di dalamnya.
- Status `aria-expanded` dan atribut `hidden` harus selalu sinkron.
- Ikon chevron berputar ketika panel terbuka.
- Tinggi panel tidak boleh memakai nilai `height` tetap.
- Konten panjang harus berkembang mengikuti isi.

---

## 7. Konten Persyaratan Pelayanan

> Catatan implementasi: ejaan pada tampilan publik dinormalisasi agar lebih mudah dibaca, tetapi makna persyaratan dari poster tidak boleh diubah.

### 7.1 Pengajuan Kartu Keluarga

#### A. Penerbitan KK Baru

1. Surat pengantar RT/RW.
2. KK lama asli dan KTP-el asli.
3. Buku nikah, kutipan akta perceraian, atau akta perkawinan.
4. Surat kelahiran dari bidan asli.

#### B. Penerbitan KK Perubahan Data

1. Surat pengantar RT/RW.
2. KK lama asli dan KTP-el asli.
3. Surat keterangan atau bukti pendukung perubahan data.

#### C. Penerbitan KK Hilang/Rusak

1. Surat pengantar RT/RW.
2. KTP-el asli.
3. Surat keterangan kehilangan dari kepolisian.

---

### 7.2 Pengajuan Akta Kematian

1. Surat pengantar RT/RW.
2. KTP pelapor dalam satu KK; pelapor dapat berasal dari RT sesuai ketentuan petugas.
3. KK dan KTP almarhum.
4. Fotokopi KTP dua orang saksi.
5. Materai.

> Teks butir kedua pada poster perlu dikonfirmasi kembali kepada perangkat desa sebelum data dianggap final, karena keterbacaan sumber gambar terbatas.

---

### 7.3 Administrasi Nikah

1. Surat pengantar RT/RW.
2. Fotokopi Kartu Keluarga.
3. Fotokopi KTP-el.
4. Fotokopi akta kelahiran atau ijazah.
5. Fotokopi KTP dua orang saksi dan wali.
6. Fotokopi kutipan buku nikah orang tua calon pengantin wanita.
7. Akta cerai atau akta kematian asli bagi calon pengantin yang berstatus duda/janda.
8. Pas foto:
   - ukuran 3 × 4 sebanyak 5 lembar;
   - ukuran 2 × 3 sebanyak 5 lembar;
   - ukuran 4 × 6 sebanyak 2 lembar.
9. Materai.
10. Surat kesehatan sebagai syarat nikah dari puskesmas.

---

### 7.4 SKCK

1. Surat pengantar RT/RW.
2. Kartu Keluarga asli.
3. KTP-el asli.
4. Pas foto ukuran 4 × 6 sebanyak 6 lembar.
5. Wajib memiliki BPJS.

Catatan warna latar foto:

- tahun ganjil: latar merah;
- tahun genap: latar biru.

---

### 7.5 Pengajuan Akta Kelahiran

1. Surat pengantar RT/RW.
2. KK dan KTP orang tua asli.
3. Buku nikah asli.
4. Surat kelahiran dari bidan atau rumah sakit asli.
5. Fotokopi KTP dua orang saksi.
6. Materai.

---

### 7.6 Pindah Tempat (Masuk/Keluar)

1. Surat pengantar RT/RW.
2. Kartu Keluarga lama asli.
3. KTP asli.
4. Materai.

Catatan:

- meminta atau membawa surat pindah dari desa yang bersangkutan.

---

### 7.7 Kartu Identitas Anak (KIA)

1. Surat pengantar RT/RW.
2. Akta kelahiran asli.
3. Kartu Keluarga asli.
4. Pas foto ukuran 3 × 4 sebanyak 2 lembar untuk anak usia di atas 5 tahun.
5. KTP orang tua asli.

---

### 7.8 BPJS

1. Surat pengantar RT/RW.
2. Kartu Keluarga asli.
3. KTP-el asli.
4. Buku rekening bank.

---

## 8. Konten Sidebar: Alur Pelayanan

Accordion **Alur Pelayanan** menampilkan vertical stepper dengan enam tahap.

### Tahap 1 — Pemohon

Pemohon meminta surat pengantar kepada Ketua RT setempat dengan membawa:

- Kartu Keluarga;
- KTP-el.

### Tahap 2 — Ketua RT

Ketua RT:

- mengisi surat pengantar sesuai keperluan pemohon;
- menandatangani surat;
- memberikan stempel.

### Tahap 3 — Pemohon

Pemohon membawa seluruh persyaratan lengkap ke kantor pelayanan desa.

### Tahap 4 — Ruang Pelayanan

Petugas pelayanan:

- memeriksa kelengkapan berkas;
- membuat surat yang diperlukan;
- melakukan registrasi dokumen.

### Tahap 5 — Petugas

Petugas mengunggah berkas yang sudah diregister ke aplikasi **SIPEDULI** apabila layanan memerlukan proses lanjutan.

### Tahap 6 — Hasil

- Surat yang tidak perlu diunggah ke aplikasi dapat langsung digunakan.
- Surat yang harus diunggah menunggu persetujuan Dukcapil Kabupaten Malang.
- Estimasi proses yang dicantumkan pada poster adalah `1 × 24 jam`.

---

## 9. Konten Sidebar: Tata Cara Pengajuan

Accordion **Tata Cara Pengajuan** menampilkan panduan praktis berikut:

1. Pilih jenis pelayanan yang dibutuhkan.
2. Buka daftar persyaratan dan siapkan dokumen asli serta fotokopi yang diminta.
3. Minta surat pengantar RT/RW.
4. Datang ke Kantor Desa Sukomulyo pada jam pelayanan.
5. Serahkan dokumen kepada petugas untuk pemeriksaan dan registrasi.
6. Simpan informasi atau bukti registrasi yang diberikan petugas.
7. Ambil dokumen langsung apabila tidak memerlukan proses Dukcapil.
8. Untuk dokumen yang diproses melalui SIPEDULI, tunggu konfirmasi petugas atau hubungi WhatsApp pelayanan.

Tambahkan pemberitahuan:

```text
Pastikan seluruh dokumen lengkap sebelum datang agar proses pelayanan tidak tertunda.
```

---

## 10. Card Informasi Pelayanan

Di bawah dua accordion sidebar, tampilkan card statis.

### Isi card

| Informasi | Nilai |
|---|---|
| Hari pelayanan | Senin–Jumat |
| Jam pelayanan | 08.00–16.00 WIB |
| Waktu istirahat | 12.00–13.00 WIB |
| Hari libur | Sabtu, Minggu, dan tanggal merah |
| WhatsApp | 085731625435 |
| Biaya | Gratis / tanpa dipungut biaya |

### Tombol WhatsApp

Gunakan tautan:

```text
https://wa.me/6285731625435
```

Pesan awal yang disarankan:

```text
Halo, saya ingin menanyakan informasi layanan administrasi Desa Sukomulyo.
```

Gunakan URL encoded pada implementasi.

Tombol harus:

- membuka tab baru;
- mempunyai `rel="noopener noreferrer"`;
- menampilkan ikon WhatsApp dan teks;
- memiliki area klik minimal 44 × 44 px.

---

## 11. Pencarian Jenis Layanan

Tambahkan pencarian lokal di atas accordion utama.

Placeholder:

```text
Cari jenis pelayanan...
```

Pencarian mencocokkan:

- judul pelayanan;
- judul subjenis;
- kata pada daftar persyaratan.

Contoh kata kunci:

```text
KK
akta
nikah
SKCK
pindah
KIA
BPJS
materai
```

### Perilaku pencarian

- Tidak memerlukan request ke server.
- Tidak mengubah URL.
- Hasil difilter melalui JavaScript.
- Jika sebuah subjenis cocok, induknya tetap ditampilkan.
- Jika hasil cocok berada di dalam panel tertutup, panel induk otomatis dibuka.
- Tombol hapus pencarian muncul ketika input berisi teks.
- Jika tidak ada hasil, tampilkan:

```text
Layanan tidak ditemukan
Coba gunakan nama dokumen atau jenis layanan lain.
```

- Pencarian harus tetap berfungsi setelah navigasi AJAX.

---

## 12. Rancangan Data

File:

```text
config/administrative_services.php
```

Struktur yang disarankan:

```php
<?php

return [
    'page' => [
        'title' => 'Layanan Administrasi',
        'description' => 'Persyaratan, alur pelayanan, jadwal, dan tata cara pengajuan administrasi masyarakat Desa Sukomulyo.',
    ],

    'services' => [
        [
            'id' => 'kartu-keluarga',
            'title' => 'Pengajuan Kartu Keluarga',
            'icon' => 'far fa-address-card',
            'keywords' => ['kk', 'kartu keluarga'],
            'children' => [
                [
                    'id' => 'kk-baru',
                    'title' => 'Penerbitan KK Baru',
                    'requirements' => [
                        'Surat pengantar RT/RW.',
                        'KK lama asli dan KTP-el asli.',
                        'Buku nikah, kutipan akta perceraian, atau akta perkawinan.',
                        'Surat kelahiran dari bidan asli.',
                    ],
                    'notes' => [],
                ],
            ],
        ],
    ],

    'service_flow' => [
        [
            'number' => 1,
            'actor' => 'Pemohon',
            'title' => 'Meminta surat pengantar',
            'description' => 'Meminta surat pengantar kepada Ketua RT dengan membawa KK dan KTP-el.',
            'icon' => 'fas fa-user',
        ],
    ],

    'submission_steps' => [
        'Pilih jenis pelayanan yang dibutuhkan.',
        'Siapkan seluruh dokumen.',
    ],

    'office' => [
        'days' => 'Senin–Jumat',
        'hours' => '08.00–16.00 WIB',
        'break' => '12.00–13.00 WIB',
        'closed' => 'Sabtu, Minggu, dan tanggal merah',
        'whatsapp_display' => '085731625435',
        'whatsapp_e164' => '6285731625435',
        'fee' => 'Gratis',
        'processing_estimate' => '1 × 24 jam untuk layanan yang memerlukan persetujuan Dukcapil',
    ],
];
```

### 12.1 Kontrak data service

```php
[
    'id' => 'akta-kelahiran',
    'title' => 'Pengajuan Akta Kelahiran',
    'icon' => 'fas fa-baby',
    'keywords' => ['akta', 'kelahiran', 'bayi'],
    'requirements' => [
        'Surat pengantar RT/RW.',
    ],
    'notes' => [],
]
```

### 12.2 Layanan bertingkat

Layanan induk menggunakan:

```php
'children' => [...]
```

Layanan tanpa subjenis menggunakan:

```php
'requirements' => [...]
```

Jangan mengisi `children` dan `requirements` sekaligus pada item yang sama.

---

## 13. Service/Presenter Halaman

Agar `PublicSiteService` tidak semakin panjang, disarankan menambahkan:

```text
app/Services/Web/AdministrativeServicePage.php
```

Contoh tanggung jawab:

```php
<?php

namespace App\Services\Web;

class AdministrativeServicePage
{
    public function data(): array
    {
        $config = config('administrative_services');

        return [
            'page' => $config['page'],
            'services' => collect($config['services']),
            'serviceFlow' => collect($config['service_flow']),
            'submissionSteps' => collect($config['submission_steps']),
            'office' => $config['office'],
        ];
    }
}
```

Integrasi pada `PublicSiteService`:

```php
public function informationDetail(
    string $section,
    AdministrativeServicePage $administrativeServicePage
): View {
    if ($section === 'layanan-administrasi') {
        return $this->render(
            'pages.administrative-services',
            $administrativeServicePage->data()
        );
    }

    // Pertahankan alur halaman informasi lain yang sudah ada.
}
```

Apabila dependency injection pada method service tidak sesuai pola proyek, service dapat di-inject melalui constructor. Yang penting, data halaman layanan tidak kembali ditulis panjang di `PublicSiteService`.

---

## 14. Struktur Blade

### 14.1 Halaman utama

File:

```text
resources/views/pages/administrative-services.blade.php
```

Kerangka:

```blade
<x-layouts.app :title="$page['title']" :description="$page['description']">
    <div
        id="administrative-service-ajax-root"
        data-ajax-scope="#administrative-service-ajax-root"
        data-page-title="{{ $page['title'] }} | Desa Sukomulyo"
        data-page-description="{{ $page['description'] }}"
    >
        <x-page-header
            :title="$page['title']"
            :description="$page['description']"
            :breadcrumbs="[
                ['label' => 'Informasi Desa', 'url' => route('informasi-publik-desa')],
                ['label' => $page['title']],
            ]"
        />

        <div class="container administration-content-container">
            <div class="administration-page-layout">
                <main
                    id="administration-requirements"
                    class="administration-main"
                    aria-labelledby="administration-requirements-heading"
                >
                    @include('administrative-services.partials.requirements')
                </main>

                <aside
                    class="administration-sidebar"
                    aria-label="Informasi alur dan pengajuan pelayanan"
                >
                    @include('administrative-services.partials.sidebar')
                </aside>
            </div>
        </div>
    </div>
</x-layouts.app>
```

### 14.2 Partial yang disarankan

```text
resources/views/administrative-services/
└── partials/
    ├── requirements.blade.php
    ├── service-accordion.blade.php
    ├── service-child-accordion.blade.php
    ├── requirement-list.blade.php
    ├── sidebar.blade.php
    ├── service-flow.blade.php
    ├── submission-guide.blade.php
    ├── office-information.blade.php
    └── empty-search.blade.php
```

### 14.3 Accordion tingkat pertama

```blade
<section
    class="administration-accordion"
    data-sidebar-accordion
    data-service-accordion
>
    @foreach ($services as $service)
        @include('administrative-services.partials.service-accordion', [
            'service' => $service,
            'open' => $loop->first,
        ])
    @endforeach
</section>
```

### 14.4 Struktur tombol accordion

```blade
<article
    class="service-accordion-item"
    data-service-item
    data-service-search="{{ Str::lower(
        $service['title'].' '.
        implode(' ', $service['keywords'] ?? [])
    ) }}"
>
    <h3 class="service-accordion-heading">
        <button
            class="service-accordion-toggle"
            type="button"
            aria-expanded="{{ $open ? 'true' : 'false' }}"
            aria-controls="service-panel-{{ $service['id'] }}"
            data-sidebar-toggle
            data-sidebar-accordion-toggle
        >
            <span class="service-accordion-icon" aria-hidden="true">
                <i class="{{ $service['icon'] }}"></i>
            </span>

            <span class="service-accordion-title">
                {{ $service['title'] }}
            </span>

            <i class="fas fa-chevron-down service-accordion-chevron" aria-hidden="true"></i>
        </button>
    </h3>

    <div
        id="service-panel-{{ $service['id'] }}"
        class="service-accordion-panel"
        data-sidebar-panel
        @if (! $open) hidden @endif
    >
        {{-- requirements atau children --}}
    </div>
</article>
```

### 14.5 Accordion tingkat kedua

Beri scope accordion tersendiri:

```blade
<div
    class="service-child-accordion"
    data-sidebar-accordion
    data-service-child-accordion
>
    {{-- tombol subjenis --}}
</div>
```

Hal ini penting agar membuka subjenis KK tidak menutup accordion layanan utama.

---

## 15. Struktur Sidebar

Gunakan pola visual `news-filter-panel` sebagai acuan, tetapi beri class khusus agar perubahan halaman layanan tidak memengaruhi halaman berita.

```blade
<div class="administration-sidebar-panel">
    <section class="widget administration-flow-widget">
        {{-- Dropdown Alur Pelayanan --}}
    </section>

    <section class="widget administration-guide-widget">
        {{-- Dropdown Tata Cara Pengajuan --}}
    </section>

    <section class="administration-office-card">
        {{-- Jadwal, WhatsApp, dan Gratis --}}
    </section>
</div>
```

### 15.1 Jangan gunakan class berita sebagai satu-satunya selector

Diperbolehkan memakai utility atau struktur umum yang sudah ada, tetapi jangan membuat halaman layanan bergantung penuh pada:

```text
.news-category-widget
.news-archive-widget
.news-popular-widget
```

Gunakan class domain halaman layanan:

```text
.administration-sidebar-panel
.administration-sidebar-widget
.service-flow-list
.submission-guide-list
.administration-office-card
```

---

## 16. Visual Accordion Utama

Gaya mengikuti contoh accordion pada gambar ketiga:

- latar putih;
- border tipis abu kehijauan;
- radius 14–18 px;
- bayangan lembut;
- ikon di sisi kiri;
- judul tebal;
- chevron di sisi kanan;
- item aktif memiliki aksen hijau muda;
- panel isi mempunyai padding lega;
- tidak memakai gradien mencolok;
- tidak memakai warna berbeda-beda untuk setiap layanan seperti poster lama;
- isi tetap mudah dibaca oleh semua kelompok usia.

### Keadaan default

```text
Border          : var(--border)
Background      : #ffffff
Icon            : var(--primary)
Judul           : var(--ink)
Chevron         : var(--muted)
```

### Keadaan aktif

```text
Border          : rgba(82, 107, 66, .32)
Background      : #eef3ea
Icon background : rgba(82, 107, 66, .12)
Judul           : var(--primary-dark)
```

### Panel isi

```text
Background : #ffffff
Border top : 1px solid var(--border)
Padding    : 18–24 px
```

---

## 17. Sistem Warna

Gunakan token yang sudah tersedia pada proyek.

Fallback warna:

```css
--administration-primary: var(--primary, #526b42);
--administration-primary-dark: var(--primary-dark, #3d5032);
--administration-soft: #eef3ea;
--administration-border: var(--border, #dce5d7);
--administration-ink: var(--ink, #1f2a1d);
--administration-muted: var(--muted, #687264);
--administration-warning: #9a6a18;
--administration-danger: #b42318;
```

Warna poster biru, hijau terang, cyan, ungu, dan merah tidak perlu disalin satu per satu. Seluruh halaman harus konsisten dengan identitas hijau Website Desa Sukomulyo.

---

## 18. CSS Class yang Disarankan

```text
.administration-page
.administration-content-container
.administration-page-layout
.administration-main
.administration-sidebar
.administration-section-header
.administration-search
.administration-search__field
.administration-search__clear
.administration-accordion
.service-accordion-item
.service-accordion-heading
.service-accordion-toggle
.service-accordion-icon
.service-accordion-title
.service-accordion-chevron
.service-accordion-panel
.service-child-accordion
.service-child-item
.service-child-toggle
.service-child-panel
.requirement-list
.requirement-list__item
.requirement-note
.administration-sidebar-panel
.administration-sidebar-widget
.service-flow-list
.service-flow-item
.service-flow-number
.service-flow-content
.submission-guide-list
.administration-office-card
.administration-office-list
.administration-whatsapp
.administration-free-badge
.administration-empty-search
```

Gunakan prefix `administration-` atau `service-` agar style tidak bocor ke halaman lain.

---

## 19. Responsive Layout

### Desktop — minimal 1100 px

```css
.administration-page-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(290px, 340px);
    gap: 34px;
    align-items: start;
}
```

- Konten utama lebih lebar.
- Sidebar berada di kanan.
- Sidebar dapat memakai `position: sticky`.
- Sticky offset harus memperhitungkan navbar.

Contoh:

```css
.administration-sidebar-panel {
    position: sticky;
    top: 110px;
}
```

Jangan gunakan sticky pada layar kecil.

### Tablet — 768 sampai 1099 px

- Tetap dua kolom apabila lebar mencukupi.
- Sidebar dapat dipersempit sekitar 280 px.
- Apabila teks terlalu sempit, ubah menjadi satu kolom.
- Jangan membuat body memiliki scrollbar horizontal.

### Mobile — di bawah 768 px

Urutan:

1. Page header.
2. Pencarian.
3. Persyaratan pelayanan.
4. Alur pelayanan.
5. Tata cara pengajuan.
6. Informasi pelayanan.

Aturan:

```css
.administration-page-layout {
    grid-template-columns: 1fr;
}

.administration-sidebar-panel {
    position: static;
}
```

- Tombol accordion memenuhi lebar layar.
- Padding horizontal halaman 12–16 px.
- Judul boleh turun ke dua baris.
- Ikon tetap berukuran konsisten.
- Area klik minimal 44 px.
- Daftar persyaratan tidak terpotong.

---

## 20. JavaScript

Buat modul khusus:

```text
resources/js/administrative-services.js
```

Import dari:

```text
resources/js/app.js
```

```js
import { initAdministrativeServices } from './administrative-services';
```

Panggil di dalam `initPublicPage()`:

```js
initAdministrativeServices();
```

### 20.1 Tanggung jawab modul

1. Menginisialisasi pencarian lokal.
2. Menormalisasi kata kunci.
3. Menampilkan atau menyembunyikan item layanan.
4. Membuka induk ketika hasil ditemukan pada child.
5. Menampilkan empty state.
6. Membersihkan event lama sebelum render AJAX berikutnya.
7. Tidak menggandakan event listener.
8. Tidak mengubah perilaku accordion global yang sudah ada.

### 20.2 Hindari double binding

Setiap root diberi penanda:

```js
if (root.dataset.administrationBound === 'true') return;
root.dataset.administrationBound = 'true';
```

### 20.3 Normalisasi pencarian

```js
const normalize = (value = '') => value
    .toLocaleLowerCase('id-ID')
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .trim();
```

### 20.4 AJAX cleanup

Dengarkan event proyek:

```js
window.addEventListener('ajax:before-render', cleanup, { once: true });
```

Cleanup harus:

- menghapus listener pencarian;
- membatalkan timer;
- menghapus referensi instance;
- tidak menghapus konten halaman secara manual.

---

## 21. Aksesibilitas

1. Gunakan elemen `<button>` untuk toggle, bukan `<div>`.
2. Setiap tombol memakai:
   - `aria-expanded`;
   - `aria-controls`.
3. Setiap panel mempunyai `id` unik.
4. Panel tertutup memakai atribut `hidden`.
5. Heading mengikuti urutan:
   - `h1` dari page header;
   - `h2` untuk bagian utama;
   - `h3` untuk layanan;
   - `h4` untuk subjenis.
6. Ikon dekoratif memakai `aria-hidden="true"`.
7. Teks judul harus tetap tersedia; jangan mengandalkan ikon.
8. Focus ring terlihat jelas.
9. Ukuran area klik minimal 44 × 44 px.
10. Jangan memakai warna sebagai satu-satunya penanda panel aktif.
11. Search input mempunyai `<label>` yang dapat disembunyikan secara visual.
12. Empty state menggunakan `role="status"` atau `aria-live="polite"`.
13. Nomor WhatsApp ditulis sebagai teks, tidak hanya ikon.
14. Badge gratis mempunyai teks lengkap:
    - `Gratis`;
    - `Tanpa dipungut biaya`.
15. Animasi dihentikan atau disederhanakan ketika:

```css
@media (prefers-reduced-motion: reduce)
```

---

## 22. SEO dan Metadata

```text
Title:
Layanan Administrasi | Desa Sukomulyo

Description:
Informasi persyaratan, alur pelayanan, jadwal, dan tata cara pengajuan administrasi di Desa Sukomulyo.

Canonical:
/informasi-desa/layanan-administrasi
```

Tambahkan structured data `GovernmentService` hanya apabila data nama organisasi, area pelayanan, dan jenis layanan sudah dipastikan valid.

Jangan membuat klaim estimasi selesai yang berbeda dari sumber poster.

---

## 23. Validasi Konten

Sebelum halaman dianggap final, lakukan konfirmasi kepada perangkat desa untuk butir yang berpotensi ambigu:

1. Teks persyaratan nomor 2 pada Akta Kematian.
2. Apakah semua surat pengantar harus melalui RT saja atau RT/RW.
3. Apakah persyaratan BPJS selalu memerlukan buku rekening bank.
4. Apakah aturan warna latar pas foto SKCK masih berlaku.
5. Apakah nomor WhatsApp `085731625435` masih aktif.
6. Apakah istilah aplikasi yang benar adalah `SIPEDULI`.
7. Apakah estimasi persetujuan Dukcapil tetap `1 × 24 jam`.
8. Apakah jam pelayanan berlaku tanpa perubahan pada hari Jumat.
9. Apakah seluruh layanan benar-benar gratis tanpa pengecualian.

Data pada halaman produksi harus mengikuti hasil konfirmasi terakhir.

---

## 24. Keamanan

Walaupun halaman hanya menampilkan informasi, tetap terapkan:

1. Escape seluruh teks dengan Blade `{{ }}`.
2. Jangan memakai `{!! !!}` untuk data persyaratan.
3. Nomor WhatsApp dan URL dibentuk dari config yang tervalidasi.
4. Jangan mengambil HTML mentah dari query parameter.
5. Search hanya memfilter DOM dan tidak menjalankan string sebagai HTML.
6. Jangan menaruh data pribadi pemohon pada halaman publik.
7. Jangan menampilkan dokumen contoh yang memuat NIK atau nomor KK.
8. Jangan membuat form pengajuan online pada tahap ini tanpa mekanisme autentikasi, validasi, perlindungan data, dan kebijakan privasi yang jelas.

---

## 25. Pengujian

### 25.1 Feature test

Tambahkan:

```text
tests/Feature/Web/AdministrativeServicePageTest.php
```

Kasus uji:

1. Route `/informasi-desa/layanan-administrasi` menghasilkan status 200.
2. Halaman memuat judul `Layanan Administrasi`.
3. Halaman memuat delapan layanan utama.
4. Halaman memuat tiga subjenis Kartu Keluarga.
5. Halaman memuat nomor WhatsApp.
6. Halaman memuat jadwal pelayanan.
7. Halaman memuat teks `Gratis`.
8. Halaman tidak memakai fallback generik lama.
9. Halaman Agenda Desa tetap dapat dibuka.
10. Halaman Bantuan Sosial tetap dapat dibuka.
11. Halaman Informasi Publik tetap dapat dibuka.

### 25.2 Unit test data

Apabila memakai class catalog/service, uji:

1. Semua `id` layanan unik.
2. Semua layanan mempunyai judul.
3. Item mempunyai `children` atau `requirements`, bukan keduanya.
4. Seluruh daftar persyaratan tidak kosong.
5. Seluruh ikon mempunyai string.
6. Nomor WhatsApp E.164 hanya berisi angka.
7. Service flow mempunyai nomor urut tanpa duplikasi.

### 25.3 Pengujian browser

Periksa:

1. Accordion utama membuka dan menutup dengan benar.
2. Hanya satu layanan utama terbuka.
3. Accordion child tidak menutup parent.
4. Keyboard `Tab`, `Enter`, dan `Space` dapat mengoperasikan toggle.
5. Chevron mengikuti status panel.
6. Search menampilkan hasil yang benar.
7. Search pada kata di child tetap menampilkan parent.
8. Empty state muncul ketika tidak ada hasil.
9. Tombol hapus pencarian bekerja.
10. WhatsApp membuka URL yang benar.
11. Sidebar sticky tidak menutup footer.
12. Mobile tidak mempunyai overflow horizontal.
13. Navigasi AJAX tidak menggandakan event.
14. Console browser tidak menghasilkan error.
15. `prefers-reduced-motion` dihormati.

---

## 26. Perintah Verifikasi

```bash
php artisan test
npm run build
```

Apabila proyek mempunyai lint atau formatter, jalankan juga perintah yang sudah tercatat pada `package.json` dan `composer.json`.

---

## 27. File yang Diubah atau Ditambahkan

### Wajib

```text
config/administrative_services.php

app/Services/Web/AdministrativeServicePage.php
app/Services/Web/PublicSiteService.php

resources/views/pages/administrative-services.blade.php

resources/views/administrative-services/partials/requirements.blade.php
resources/views/administrative-services/partials/service-accordion.blade.php
resources/views/administrative-services/partials/service-child-accordion.blade.php
resources/views/administrative-services/partials/requirement-list.blade.php
resources/views/administrative-services/partials/sidebar.blade.php
resources/views/administrative-services/partials/service-flow.blade.php
resources/views/administrative-services/partials/submission-guide.blade.php
resources/views/administrative-services/partials/office-information.blade.php
resources/views/administrative-services/partials/empty-search.blade.php

resources/js/administrative-services.js
resources/js/app.js

resources/css/app.css

tests/Feature/Web/AdministrativeServicePageTest.php
```

### Tidak perlu diubah apabila route tetap sama

```text
routes/web.php
app/Http/Controllers/Web/PublicationController.php
```

---

## 28. Larangan Implementasi

Jangan melakukan hal berikut:

```text
× mengganti view generik untuk seluruh halaman informasi;
× menulis seluruh daftar persyaratan langsung di satu file Blade;
× menyalin semua warna poster ke halaman;
× menampilkan poster sebagai satu-satunya sumber informasi;
× memakai gambar poster yang teksnya sulit dibaca sebagai pengganti HTML;
× membuat accordion menggunakan div tanpa aksesibilitas;
× memakai ID panel yang duplikat;
× membuka semua accordion sekaligus pada mobile;
× menambahkan library accordion baru;
× memakai jQuery;
× membuat sidebar fixed;
× membuat body overflow horizontal;
× membuat event listener ganda setelah navigasi AJAX;
× membuat data persyaratan palsu;
× menambah jenis layanan yang tidak terdapat pada data sumber;
× menambahkan form upload KTP/KK pada halaman publik;
× mengubah navbar, footer, autentikasi admin, dan halaman berita;
× merusak route Agenda, Bantuan Sosial, atau Informasi Publik.
```

---

## 29. Definition of Done

Implementasi selesai apabila:

1. Halaman memakai layout dua kolom seperti pola Berita Desa.
2. Persyaratan tampil dalam accordion bertingkat.
3. Kartu Keluarga mempunyai tiga dropdown child.
4. Seluruh layanan pada poster tersedia.
5. Sidebar mempunyai dropdown Alur Pelayanan.
6. Sidebar mempunyai dropdown Tata Cara Pengajuan.
7. Jadwal, WhatsApp, estimasi, dan status gratis terlihat jelas.
8. Search layanan bekerja.
9. Desktop, tablet, dan mobile tampil rapi.
10. Keyboard dan screen reader dapat mengoperasikan accordion.
11. Navigasi AJAX tidak menimbulkan double binding.
12. Tidak ada error console.
13. `npm run build` berhasil.
14. `php artisan test` berhasil.
15. Halaman informasi lainnya tidak berubah atau rusak.
16. Codex melaporkan file yang diubah dan hasil pengujian.

---

## 30. Prompt Ringkas untuk Codex

```text
Baca seluruh dokumen ARSITEKTUR_HALAMAN_LAYANAN_ADMINISTRASI.md sebelum
mengubah kode.

Kerjakan pada repository Website-Profil-Desa-Sukomulyo, branch
feature/informasi-desa.

Bangun halaman /informasi-desa/layanan-administrasi dengan view khusus.
Jangan mengganti information-detail.blade.php untuk seluruh halaman informasi.

Layout desktop harus dua kolom seperti pola halaman Berita Desa:
- kolom utama berisi persyaratan pelayanan;
- sidebar kanan berisi dropdown Alur Pelayanan dan Tata Cara Pengajuan;
- di bawahnya terdapat card jadwal, WhatsApp, dan status GRATIS.

Persyaratan menggunakan accordion bertingkat.
Layanan utama:
1. Pengajuan Kartu Keluarga;
2. Pengajuan Akta Kematian;
3. Administrasi Nikah;
4. SKCK;
5. Pengajuan Akta Kelahiran;
6. Pindah Tempat;
7. KIA;
8. BPJS.

Kartu Keluarga mempunyai child:
- Penerbitan KK Baru;
- Perubahan Data;
- Hilang/Rusak.

Pisahkan data ke config/administrative_services.php.
Jangan hardcode seluruh data di Blade.
Gunakan class dengan prefix administration- dan service-.
Gunakan warna hijau website, terutama #526b42.
Jangan menyalin warna-warni poster.

Gunakan pola toggle yang sudah tersedia pada resources/js/app.js.
Tambahkan modul administrative-services.js untuk pencarian lokal dan lifecycle AJAX.
Tidak perlu menambah library atau jQuery.

Pertahankan route, navbar, footer, autentikasi, navigasi AJAX, halaman berita,
Agenda Desa, Bantuan Sosial, dan Informasi Publik.

Pastikan:
- aria-expanded dan hidden sinkron;
- hanya satu parent terbuka;
- child tidak menutup parent;
- mobile menjadi satu kolom;
- tidak ada overflow horizontal;
- WhatsApp menuju 6285731625435;
- informasi GRATIS dan jam pelayanan terlihat.

Setelah implementasi:
- jalankan npm run build;
- jalankan php artisan test;
- laporkan seluruh file yang berubah;
- laporkan hasil pengujian;
- sebutkan butir konten yang masih memerlukan konfirmasi perangkat desa.
```

---

## 31. Ringkasan Desain Final

Halaman Layanan Administrasi menggunakan gaya bersih dan resmi:

- daftar persyaratan berada pada kolom utama;
- setiap jenis layanan berupa accordion;
- layanan dengan subjenis memakai accordion tingkat kedua;
- sidebar kanan mengikuti pola layout Berita Desa;
- sidebar hanya mempunyai dua dropdown utama:
  - Alur Pelayanan;
  - Tata Cara Pengajuan;
- jadwal, WhatsApp, dan status gratis ditampilkan pada card ringkas;
- warna hijau digunakan sebagai identitas utama;
- poster hanya menjadi sumber konten, bukan elemen utama tampilan;
- semua informasi ditulis sebagai HTML yang dapat dicari, dibaca, dan diakses;
- struktur tetap ringan tanpa library frontend tambahan.
