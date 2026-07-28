# Spesifikasi Implementasi Halaman Statistik Penduduk 2026

## 1. Tujuan

Bangun ulang bagian **Statistik Penduduk** pada website Desa Sukomulyo di branch `baru` agar:

1. Menampilkan komposisi penduduk tahun aktif, yaitu **2026**.
2. Kategori utama hanya:
   - Laki-laki
   - Perempuan
3. Menampilkan grafik pie/doughnut interaktif.
4. Menampilkan tabel jumlah dan persentase berdasarkan jenis kelamin.
5. Menampilkan riwayat pertumbuhan penduduk tahunan.
6. Pengguna dapat memilih rentang tahun, misalnya 2020–2026.
7. Tampilan konsisten dengan desain website yang sudah ada.
8. Data tidak menggunakan angka contoh palsu pada halaman produksi.
9. Halaman tetap responsif, aksesibel, dan kompatibel dengan navigasi AJAX yang sudah digunakan proyek.

---

## 2. Konteks Proyek yang Harus Dipertahankan

Implementasi dilakukan pada:

- Repository: `Website-Profil-Desa-Sukomulyo`
- Branch: `baru`
- Backend: Laravel 12 dan PHP 8.2
- Frontend bundler: Vite
- CSS: Tailwind CSS 4 dan stylesheet proyek yang sudah ada
- JavaScript: ES Module
- Halaman publik statistik yang sudah tersedia:
  - `/data-statistik/penduduk`
  - `/kependudukan/jenis-kelamin`
- Controller publik:
  - `app/Http/Controllers/Web/VillageStatisticController.php`
- Service statistik:
  - `app/Services/PopulationStatistics.php`
- Service halaman publik:
  - `app/Services/Web/PublicSiteService.php`
- View yang sekarang digunakan:
  - `resources/views/pages/statistic-detail.blade.php`
- Entry JavaScript:
  - `resources/js/app.js`
- Entry CSS:
  - `resources/css/app.css`

Jangan mengubah arsitektur besar proyek, layout utama, navbar, footer, autentikasi admin, dan sistem navigasi AJAX yang sudah berjalan.

---

## 3. Library Grafik

Gunakan **Apache ECharts**.

### Alasan

- Gratis dan open-source.
- Mendukung pie/doughnut chart, line chart, tooltip, legend, data zoom, animasi, ekspor gambar, dan event interaktif.
- Dapat digunakan melalui Vite tanpa CDN.
- Lebih kuat untuk pengembangan statistik desa berikutnya dibanding library grafik yang terlalu sederhana.
- Tidak membutuhkan jQuery.

### Instalasi

Jalankan:

```bash
npm install echarts
npm run dev
```

Untuk produksi:

```bash
npm run build
```

Jangan menggunakan CDN agar dependency tercatat di `package.json`, build lebih konsisten, dan halaman tetap mengikuti sistem Vite proyek.

### Import yang disarankan

Gunakan modular import agar ukuran bundle tidak terlalu besar:

```js
import * as echarts from 'echarts/core';
import { PieChart, LineChart } from 'echarts/charts';
import {
    DatasetComponent,
    GridComponent,
    LegendComponent,
    TitleComponent,
    ToolboxComponent,
    TooltipComponent,
    DataZoomComponent,
} from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
    PieChart,
    LineChart,
    DatasetComponent,
    GridComponent,
    LegendComponent,
    TitleComponent,
    ToolboxComponent,
    TooltipComponent,
    DataZoomComponent,
    CanvasRenderer,
]);
```

Tidak perlu memasang Chart.js, ApexCharts, Highcharts, DataTables, atau library dropdown lain untuk halaman ini.

---

## 4. Aturan Data dan Istilah

### 4.1 Perbedaan sensus dan data tahunan

Jangan menyebut seluruh data 2020–2026 sebagai “hasil sensus”.

Gunakan aturan berikut:

- Data 2020 boleh diberi sumber **Sensus Penduduk 2020** apabila angka tersebut benar-benar berasal dari BPS.
- Data 2021–2026 sebaiknya disebut:
  - Data administrasi kependudukan desa;
  - Data registrasi penduduk;
  - Rekapitulasi penduduk per 31 Desember; atau
  - Data semester terakhir yang telah diverifikasi.
- Sensus penduduk nasional dilakukan dalam siklus sepuluh tahunan.
- Grafik lima tahun adalah grafik **perkembangan data penduduk tahunan**, bukan grafik lima tahunan sensus.

Tampilkan catatan sumber di bawah grafik:

> Data 2020 dapat menggunakan basis Sensus Penduduk 2020. Data tahunan setelahnya merupakan rekapitulasi administrasi kependudukan desa sesuai tanggal referensi masing-masing dan bukan sensus baru.

### 4.2 Tahun aktif

Untuk implementasi sekarang:

```php
$currentYear = 2026;
```

Akan tetapi, jangan hard-code tahun di banyak file.

Gunakan satu sumber:

```php
$currentYear = (int) config('village.population_year', now()->year);
```

Tambahkan ke `config/village.php`:

```php
return [
    'population_year' => env('VILLAGE_POPULATION_YEAR', now()->year),
];
```

Tambahkan ke `.env.example`:

```env
VILLAGE_POPULATION_YEAR=2026
```

Dengan cara ini, tahun dapat diganti menjadi 2027 tanpa membongkar view dan JavaScript.

---

## 5. Struktur Halaman

Target utama:

```text
/data-statistik/penduduk
```

Halaman `/kependudukan/jenis-kelamin` boleh menggunakan komponen Blade dan data yang sama agar tidak terjadi duplikasi.

Urutan halaman:

1. Page header dan breadcrumb.
2. Ringkasan singkat tahun aktif.
3. Grafik pie/doughnut komposisi penduduk.
4. Tabel komposisi laki-laki dan perempuan.
5. Tombol/menu analitik.
6. Panel pertumbuhan penduduk tahunan.
7. Filter rentang tahun.
8. Grafik garis.
9. Tabel riwayat tahunan.
10. Catatan sumber dan tanggal pembaruan.

---

## 6. Desain Bagian Atas

### 6.1 Header

Judul:

```text
Statistik Penduduk
```

Deskripsi:

```text
Komposisi dan perkembangan jumlah penduduk Desa Sukomulyo berdasarkan data administrasi kependudukan yang telah dipublikasikan.
```

Breadcrumb:

```text
Data Desa > Statistik Penduduk
```

### 6.2 Ringkasan tahun

Di bawah header, tampilkan baris informasi:

- Badge: `Tahun Data 2026`
- Badge: `Data Terakhir Diperbarui: [tanggal]`
- Badge sumber: `Administrasi Kependudukan Desa`

Jangan memakai terlalu banyak kartu besar. Fokus utama halaman harus tetap grafik dan tabel.

---

## 7. Grafik Pie Interaktif

### 7.1 Bentuk grafik

Gunakan **doughnut chart**, yaitu varian pie chart dengan ruang kosong di tengah.

Judul panel:

```text
Komposisi Penduduk Tahun 2026
```

Subjudul:

```text
Perbandingan jumlah penduduk laki-laki dan perempuan.
```

Bagian tengah doughnut menampilkan:

```text
TOTAL
x.xxx jiwa
```

### 7.2 Data

Data hanya dua kategori:

```js
[
    { name: 'Laki-laki', value: maleCount },
    { name: 'Perempuan', value: femaleCount },
]
```

Nilai harus berasal dari database, bukan angka statis.

### 7.3 Interaksi

Saat pengguna hover atau fokus:

- Sektor sedikit membesar.
- Tooltip menampilkan:
  - Kategori;
  - Jumlah jiwa;
  - Persentase;
  - Tahun data.
- Legend dapat diklik untuk menyembunyikan/menampilkan kategori.
- Angka menggunakan format Indonesia, misalnya `1.245 jiwa`.
- Persentase maksimal dua angka desimal.

Contoh tooltip:

```text
Laki-laki
1.245 jiwa
50,37%
Tahun 2026
```

### 7.4 Kondisi data kosong

Apabila total penduduk `0`:

- Jangan membagi grafik menjadi dua sektor palsu.
- Tampilkan empty state:

```text
Data komposisi penduduk tahun 2026 belum tersedia.
```

- Gunakan `stillShowZeroSum: false`.
- Tabel tetap muncul dengan nilai `0`.

---

## 8. Tabel Komposisi Penduduk

Di bawah grafik, buat tabel satu baris dengan struktur:

| Tahun | Jumlah Laki-laki | Persentase Laki-laki | Jumlah Perempuan | Persentase Perempuan | Jumlah Total |
|---|---:|---:|---:|---:|---:|
| 2026 | data DB | hasil hitung | data DB | hasil hitung | hasil penjumlahan |

### Aturan hitung

```text
total = male + female
male_percentage = male / total × 100
female_percentage = female / total × 100
```

Jika `total = 0`, kedua persentase menjadi `0`.

### Format

- Jumlah: `1.245 jiwa`
- Persentase: `50,37%`
- Total: `2.471 jiwa`
- Header tabel tetap terbaca pada mobile.
- Pada layar kecil, gunakan horizontal scroll dengan indikator halus.
- Jangan memotong nama kolom secara tidak jelas.
- Tambahkan `caption` yang dapat dibaca screen reader:

```html
<caption class="sr-only">
    Komposisi penduduk Desa Sukomulyo tahun 2026 berdasarkan jenis kelamin
</caption>
```

---

## 9. Tombol Analitik dan Menu “Tusuk Sate”

Jangan memakai ikon tiga titik tanpa keterangan karena membingungkan pengguna umum.

Gunakan tombol:

```text
[ikon tiga titik vertikal] Analisis Data
```

Atribut aksesibilitas:

```html
aria-expanded="false"
aria-controls="population-analytics-menu"
aria-haspopup="menu"
```

Menu berisi:

1. `Lihat pertumbuhan 5 tahun`
2. `Lihat data sejak 2020`
3. `Unduh grafik sebagai gambar`

Saat opsi pertama atau kedua dipilih:

- Scroll halus ke panel pertumbuhan.
- Atur rentang tahun secara otomatis.
- Pastikan panel pertumbuhan dalam keadaan terbuka.
- Fokus keyboard dipindahkan ke judul panel.

Untuk mobile, menu dapat tampil sebagai popover kecil atau bottom sheet ringan. Jangan memasang library popover tambahan; gunakan JavaScript native dan CSS proyek.

Sebagai tombol utama yang selalu terlihat, tambahkan juga:

```text
Lihat Tren Penduduk
```

Ikon tiga titik hanya menjadi menu tindakan tambahan, bukan satu-satunya cara membuka grafik.

---

## 10. Panel Pertumbuhan Penduduk

### 10.1 Judul

```text
Pertumbuhan Penduduk Tahunan
```

Deskripsi:

```text
Perubahan jumlah penduduk berdasarkan data tahunan yang telah dipublikasikan.
```

### 10.2 Periode default

Karena tahun aktif 2026, preset lima data tahunan adalah:

```text
2022–2026
```

Perhitungan:

```php
$defaultFromYear = max($minimumAvailableYear, $currentYear - 4);
$defaultToYear = $currentYear;
```

Lima tahun berarti lima titik data tahunan, bukan selisih lima tahun yang menghasilkan enam titik.

### 10.3 Preset

Sediakan tombol:

- `5 Tahun Terakhir`
- `Sejak 2020`
- `Semua Data`

### 10.4 Filter rentang

Gunakan dua select:

```text
Dari Tahun: [2020]
Sampai Tahun: [2026]
```

Aturan:

- Tahun awal tidak boleh lebih besar dari tahun akhir.
- Tahun akhir tidak boleh melebihi tahun aktif.
- Hanya tampilkan tahun yang tersedia di database.
- Jika rentang tidak valid, tampilkan pesan singkat.
- Grafik selalu diurutkan kronologis dari tahun lama ke tahun baru.

Jangan membalik urutan sumbu grafik ketika pengguna memilih urutan tabel menurun.

### 10.5 Sorting tabel

Tabel riwayat dapat memiliki kontrol:

```text
Urutan: Terlama ke Terbaru | Terbaru ke Terlama
```

Sorting hanya memengaruhi tabel, bukan urutan grafik.

---

## 11. Grafik Garis Pertumbuhan

### 11.1 Series

Gunakan tiga series yang dapat diaktifkan melalui legend:

1. `Total Penduduk`
2. `Laki-laki`
3. `Perempuan`

Default:

- Total Penduduk: aktif.
- Laki-laki: aktif.
- Perempuan: aktif.

Total harus paling menonjol secara ketebalan garis, tetapi tetap mengikuti warna desain website.

### 11.2 Sumbu

- Sumbu X: tahun.
- Sumbu Y: jumlah penduduk dalam jiwa.
- Jangan memulai sumbu Y dari angka yang menyesatkan apabila rentang terlalu sempit.
- Format label menggunakan `Intl.NumberFormat('id-ID')`.
- Tidak perlu sumbu Y kedua untuk persentase pertumbuhan.

### 11.3 Tooltip

Tooltip tahun harus menampilkan:

```text
2025
Total Penduduk: 2.471 jiwa
Laki-laki: 1.245 jiwa
Perempuan: 1.226 jiwa
Perubahan: +36 jiwa
Pertumbuhan: +1,48%
```

Rumus pertumbuhan:

```text
growth_percentage =
(current_total - previous_total) / previous_total × 100
```

Untuk tahun pertama dalam rentang:

```text
Perubahan: —
Pertumbuhan: —
```

Jika total tahun sebelumnya `0`, persentase pertumbuhan harus `null`, bukan infinity.

### 11.4 Interaksi tambahan

Aktifkan:

- Tooltip axis.
- Crosshair atau axis pointer.
- Legend toggle.
- Data zoom slider jika data lebih dari 8 tahun.
- Toolbox `saveAsImage`.
- Resize responsif.
- `connectNulls: false`.

Jika ada tahun yang datanya belum tersedia, jangan mengarang atau menginterpolasi angka. Tampilkan gap pada garis.

---

## 12. Tabel Riwayat Tahunan

Di bawah grafik garis, tampilkan tabel:

| Tahun | Laki-laki | Perempuan | Total | Perubahan | Pertumbuhan | Sumber |
|---|---:|---:|---:|---:|---:|---|
| 2026 | data | data | data | +x jiwa | +x,xx% | Administrasi Desa |

Fungsi tabel:

- Menjadi fallback bagi pengguna yang tidak dapat membaca grafik.
- Memudahkan verifikasi angka.
- Bisa diurutkan naik/turun berdasarkan tahun.
- Tidak perlu pagination untuk data maksimal beberapa puluh tahun.
- Jangan menggunakan DataTables.

---

## 13. Model Data Historis

Data penduduk aktif saat ini hanya cukup untuk komposisi terbaru. Untuk grafik tahunan yang dapat dipertanggungjawabkan, buat tabel snapshot.

### 13.1 Migration

Nama tabel:

```text
population_yearly_snapshots
```

Kolom:

```php
Schema::create('population_yearly_snapshots', function (Blueprint $table) {
    $table->id();
    $table->unsignedSmallInteger('year')->unique();
    $table->unsignedBigInteger('male_count')->default(0);
    $table->unsignedBigInteger('female_count')->default(0);
    $table->date('reference_date')->nullable();
    $table->string('source', 150)->nullable();
    $table->text('notes')->nullable();
    $table->boolean('is_published')->default(false);
    $table->timestamps();
});
```

Jangan menyimpan `total_count` apabila nilainya selalu merupakan hasil `male_count + female_count`. Hitung melalui accessor/service agar tidak terjadi data tidak sinkron.

### 13.2 Model

Buat:

```text
app/Models/PopulationYearlySnapshot.php
```

Tambahkan:

- `$fillable`
- casts:
  - `year` integer
  - `male_count` integer
  - `female_count` integer
  - `reference_date` date
  - `is_published` boolean
- accessor `total_count`

Contoh:

```php
protected function totalCount(): Attribute
{
    return Attribute::get(
        fn () => $this->male_count + $this->female_count
    );
}
```

### 13.3 Data 2026

Komposisi 2026 pada panel atas harus mengambil data aktif dari tabel `residents` agar selalu terbaru.

Snapshot 2026 digunakan untuk grafik tahunan setelah dipublikasikan dan harus mempunyai tanggal referensi yang jelas.

Pilihan implementasi:

- Jika snapshot 2026 sudah tersedia dan dipublikasikan, gunakan snapshot.
- Jika belum tersedia, grafik boleh memakai ringkasan aktif 2026 sebagai titik sementara dengan label sumber:
  `Data aktif per [tanggal pembaruan]`.
- Jangan menyimpan snapshot otomatis setiap halaman dibuka.

---

## 14. Service Statistik

Perluas:

```text
app/Services/PopulationStatistics.php
```

Tambahkan method:

```php
public function genderSummary(): array
```

Output:

```php
[
    'year' => 2026,
    'male' => 0,
    'female' => 0,
    'total' => 0,
    'male_percentage' => 0.0,
    'female_percentage' => 0.0,
    'updated_at' => null,
    'source' => null,
]
```

Tambahkan:

```php
public function yearlyTrend(?int $fromYear = null, ?int $toYear = null): array
```

Output setiap baris:

```php
[
    'year' => 2022,
    'male' => 0,
    'female' => 0,
    'total' => 0,
    'change' => null,
    'growth_percentage' => null,
    'source' => null,
    'reference_date' => null,
]
```

Aturan:

1. Hanya ambil snapshot dengan `is_published = true`.
2. Urutkan naik berdasarkan tahun sebelum menghitung perubahan.
3. Filter rentang setelah validasi.
4. Hitung perubahan terhadap tahun sebelumnya yang tersedia.
5. Jangan menghitung terhadap data yang tidak dipublikasikan.
6. Gunakan query database, jangan memuat seluruh tabel `residents` jika hanya membutuhkan count.
7. Gunakan clone query seperti pola service yang sudah ada.

---

## 15. Request Validation

Buat:

```text
app/Http/Requests/Web/PopulationTrendRequest.php
```

Validasi:

```php
return [
    'from_year' => ['nullable', 'integer', 'min:1900', 'max:' . config('village.population_year')],
    'to_year' => ['nullable', 'integer', 'min:1900', 'max:' . config('village.population_year'), 'gte:from_year'],
    'sort' => ['nullable', Rule::in(['asc', 'desc'])],
];
```

Filter boleh bekerja melalui query string:

```text
/data-statistik/penduduk?from_year=2020&to_year=2026&sort=asc
```

Namun interaksi preset di frontend sebaiknya langsung memfilter dataset yang sudah dikirim ke halaman agar tidak selalu reload.

---

## 16. Controller dan PublicSiteService

### 16.1 Controller

Perbarui method `show()` agar request rentang dapat diteruskan secara bersih, atau buat method khusus penduduk jika struktur menjadi lebih mudah dipelihara.

Pilihan yang disarankan:

```php
Route::get('/data-statistik/penduduk', [VillageStatisticController::class, 'population'])
    ->name('data-statistik.population');
```

Tetap pertahankan route lama agar tautan yang ada tidak rusak.

### 16.2 PublicSiteService

Pada halaman `penduduk`, kirim data:

```php
[
    'page' => [...],
    'genderSummary' => $populationStatistics->genderSummary(),
    'populationTrend' => $populationStatistics->yearlyTrend(),
    'availableYears' => [...],
    'defaultRange' => [
        'from' => 2022,
        'to' => 2026,
    ],
]
```

Jangan membangun konfigurasi ECharts lengkap di PHP. PHP hanya mengirim data mentah yang aman. Konfigurasi visual dibuat di JavaScript.

Gunakan `@json()` atau `Js::from()`:

```blade
data-gender='{{ Js::from($genderSummary) }}'
data-trend='{{ Js::from($populationTrend) }}'
```

Lebih aman menggunakan elemen script JSON:

```blade
<script type="application/json" id="population-statistics-data">
    {{ Js::from([
        'summary' => $genderSummary,
        'trend' => $populationTrend,
        'availableYears' => $availableYears,
    ]) }}
</script>
```

---

## 17. Blade View

Disarankan membuat view khusus:

```text
resources/views/pages/population-statistics.blade.php
```

Jangan menumpuk seluruh fitur baru ke dalam `statistic-detail.blade.php` karena halaman penduduk memiliki kebutuhan visual dan interaksi yang lebih kompleks daripada kategori statistik lain.

Gunakan komponen kecil bila diperlukan:

```text
resources/views/components/statistics/chart-panel.blade.php
resources/views/components/statistics/source-note.blade.php
resources/views/components/statistics/year-range-filter.blade.php
```

Elemen utama:

```html
<section data-population-statistics>
    <div data-population-pie></div>
    <table>...</table>

    <button data-population-trend-toggle>
        Lihat Tren Penduduk
    </button>

    <section id="population-trend-panel" data-population-trend-panel>
        <form data-population-year-filter>...</form>
        <div data-population-line></div>
        <table data-population-history-table>...</table>
    </section>
</section>
```

Chart container harus memiliki tinggi eksplisit:

```css
.population-pie-chart {
    width: 100%;
    min-height: 360px;
}

.population-line-chart {
    width: 100%;
    min-height: 420px;
}
```

Tanpa tinggi eksplisit, ECharts tidak akan dirender dengan benar.

---

## 18. JavaScript

Buat file:

```text
resources/js/population-statistics.js
```

Export:

```js
export const initPopulationStatistics = () => {};
```

Import ke `resources/js/app.js`:

```js
import { initPopulationStatistics } from './population-statistics';
```

Panggil dari `initPublicPage()`:

```js
initPopulationStatistics();
```

### 18.1 Pencegahan binding ganda

Karena proyek memakai navigasi AJAX:

```js
if (!root || root.dataset.bound === 'true') return;
root.dataset.bound = 'true';
```

### 18.2 Cleanup

Sebelum konten halaman diganti:

```js
window.addEventListener('ajax:before-render', () => {
    pieChart?.dispose();
    lineChart?.dispose();
    resizeObserver?.disconnect();
    controller.abort();
}, { once: true, signal });
```

Jangan membiarkan instance ECharts lama tetap hidup karena dapat menyebabkan memory leak dan event listener ganda.

### 18.3 Resize

Gunakan `ResizeObserver`:

```js
const resizeObserver = new ResizeObserver(() => {
    pieChart?.resize();
    lineChart?.resize();
});
```

Observe container atau panel chart.

### 18.4 Reduced motion

Jika:

```js
window.matchMedia('(prefers-reduced-motion: reduce)').matches
```

maka:

```js
animation: false
```

### 18.5 Parsing data

Gunakan `JSON.parse()` pada elemen `application/json`.

Tangani error parsing dengan empty state, jangan biarkan seluruh JavaScript halaman gagal.

---

## 19. Tampilan dan CSS

Tambahkan kelas khusus pada `resources/css/app.css`, misalnya:

```text
.population-statistics
.population-statistics__header
.population-chart-card
.population-chart-card__heading
.population-chart-card__canvas
.population-summary-table
.population-analytics
.population-analytics-menu
.population-range-filter
.population-trend-panel
.population-source-note
.population-empty-state
```

### Prinsip visual

- Background kartu putih atau mengikuti panel yang sudah ada.
- Border lembut.
- Radius konsisten dengan komponen website.
- Shadow tipis, jangan terlalu futuristik.
- Hindari gradien berlebihan.
- Gunakan ruang putih yang cukup.
- Gunakan tipografi proyek.
- Grafik tidak boleh terlihat seperti template AI.
- Judul dan angka harus lebih dominan daripada dekorasi.
- Warna laki-laki dan perempuan harus konsisten antara pie, line, legend, dan tabel.
- Jangan mengandalkan warna saja; selalu tampilkan label.

### Responsive

Desktop:

- Grafik pie dan ringkasan dapat disusun dua kolom jika ruang cukup.
- Tabel tetap di bawah atau di sisi kanan sesuai lebar aktual.

Tablet:

- Satu kolom.
- Menu analitik tidak keluar viewport.

Mobile:

- Chart tinggi sekitar 300–340 px.
- Legend berada di bawah chart.
- Tabel horizontal scroll.
- Filter tahun menjadi dua kolom atau menumpuk.
- Tombol mempunyai area sentuh minimum sekitar 44 × 44 px.

---

## 20. Admin Snapshot Tahunan

Agar data grafik dapat dikelola tanpa mengubah kode, tambahkan menu admin:

```text
Kependudukan > Statistik Tahunan
```

Route:

```php
Route::resource(
    'statistik-tahunan',
    PopulationYearlySnapshotController::class
)->except('show');
```

Fitur admin minimum:

- Tambah data tahun.
- Edit data.
- Hapus data dengan konfirmasi.
- Publish/unpublish.
- Kolom:
  - Tahun
  - Jumlah laki-laki
  - Jumlah perempuan
  - Tanggal referensi
  - Sumber
  - Catatan
  - Status publikasi
- Validasi tahun unik.
- Preview total otomatis.
- Preview persentase otomatis.
- Activity log mengikuti pola admin yang sudah ada.
- Invalidate cache statistik publik setelah create/update/delete/publish.

Jangan izinkan data negatif.

---

## 21. Cache

Halaman statistik publik saat ini menggunakan cache. Pertahankan cache, tetapi pastikan cache dihapus saat:

- Data penduduk berubah.
- Import penduduk selesai.
- Snapshot tahunan ditambah.
- Snapshot diubah.
- Snapshot dipublikasikan atau disembunyikan.
- Snapshot dihapus.

Tambahkan cache key khusus bila diperlukan:

```text
PUBLIC_POPULATION_STATISTICS
PUBLIC_POPULATION_TREND
```

TTL yang disarankan:

```text
10 menit
```

Untuk admin, data tidak perlu memakai cache publik.

---

## 22. Aksesibilitas

Wajib:

- Chart mempunyai `role="img"`.
- Chart mempunyai `aria-label` yang menjelaskan isi.
- Sediakan tabel sebagai representasi data alternatif.
- Tombol menu mendukung Enter, Space, Escape, dan klik di luar.
- Fokus tidak hilang saat menu ditutup.
- Legend dan warna memiliki kontras cukup.
- Tooltip bukan satu-satunya tempat informasi tersedia.
- Gunakan `aria-live="polite"` untuk status perubahan filter.
- Jangan menyembunyikan outline fokus.
- Empty state dapat dibaca screen reader.

Contoh:

```html
<div
    class="population-pie-chart"
    data-population-pie
    role="img"
    aria-label="Grafik komposisi penduduk tahun 2026: laki-laki dan perempuan">
</div>
```

---

## 23. Keamanan dan Integritas Data

- Semua data berasal dari server dan di-escape menggunakan Blade/`Js::from`.
- Jangan menyisipkan JSON mentah dengan string concatenation.
- Validasi seluruh parameter tahun.
- Jangan percaya nilai total dari request.
- Total dan persentase dihitung di server.
- Jangan menampilkan snapshot belum dipublikasikan pada halaman publik.
- Jangan menggunakan angka dummy di production.
- Seeder hanya untuk environment lokal/testing.
- Sumber data dan tanggal referensi harus terlihat.

---

## 24. Pengujian

### Feature test

Buat test untuk:

1. Halaman penduduk dapat diakses.
2. Data laki-laki dan perempuan tampil.
3. Total merupakan penjumlahan keduanya.
4. Persentase tidak membagi dengan nol.
5. Snapshot unpublished tidak tampil.
6. Filter 2020–2026 mengembalikan rentang benar.
7. `from_year > to_year` ditolak.
8. Tahun di atas tahun aktif ditolak.
9. Data trend diurutkan kronologis.
10. Tahun tanpa data tidak diisi dengan angka palsu.

### Unit test

Uji service:

```text
genderSummary()
yearlyTrend()
```

Kasus:

- Normal.
- Total nol.
- Hanya satu tahun.
- Tahun sebelumnya nol.
- Ada gap tahun.
- Ada snapshot unpublished.
- Persentase menghasilkan maksimal dua desimal.

### Frontend manual test

- Desktop.
- Tablet.
- Mobile.
- Navigasi langsung.
- Navigasi AJAX.
- Resize browser.
- Back/forward browser.
- Keyboard only.
- Reduced motion.
- Data kosong.
- Data sangat besar.
- Menu analitik dibuka dan ditutup berulang kali.

---

## 25. Acceptance Criteria

Implementasi dianggap selesai jika:

- [ ] Halaman menampilkan tahun aktif 2026.
- [ ] Pie/doughnut hanya berisi laki-laki dan perempuan.
- [ ] Total tampil di tengah chart.
- [ ] Tooltip menampilkan jumlah dan persentase.
- [ ] Tabel menampilkan jumlah laki-laki, persentase laki-laki, jumlah perempuan, persentase perempuan, dan total.
- [ ] Grafik pertumbuhan default menampilkan lima titik tahunan terbaru.
- [ ] Pengguna dapat memilih rentang 2020–2026.
- [ ] Grafik garis menampilkan total, laki-laki, dan perempuan.
- [ ] Tabel riwayat menampilkan perubahan dan pertumbuhan.
- [ ] Data 2020–2026 tidak semuanya disebut sensus.
- [ ] Data kosong mempunyai empty state.
- [ ] Tidak ada angka dummy pada production.
- [ ] ECharts diinstal melalui npm.
- [ ] Chart tidak rusak setelah navigasi AJAX.
- [ ] Chart responsif ketika ukuran layar berubah.
- [ ] Semua instance chart dibersihkan sebelum AJAX render.
- [ ] Snapshot tahunan dapat dikelola admin.
- [ ] Cache dihapus setelah data berubah.
- [ ] Test backend lulus.
- [ ] `npm run build` berhasil.
- [ ] Tidak ada error console.

---

## 26. Urutan Pengerjaan untuk Codex

Kerjakan bertahap:

### Tahap 1 — Audit

1. Checkout branch `baru`.
2. Baca route, controller, service, view, JavaScript, CSS, dan model penduduk.
3. Jangan menghapus fungsi statistik lama yang dipakai halaman lain.

### Tahap 2 — Dependency

1. Install `echarts`.
2. Pastikan `package.json` dan lock file berubah.
3. Pastikan Vite build berhasil.

### Tahap 3 — Database

1. Buat migration snapshot tahunan.
2. Buat model.
3. Buat factory bila diperlukan untuk test.
4. Jangan seed angka produksi palsu.

### Tahap 4 — Backend

1. Tambahkan method summary jenis kelamin.
2. Tambahkan method trend tahunan.
3. Tambahkan validasi rentang tahun.
4. Tambahkan data page khusus penduduk.
5. Tambahkan cache dan invalidation.

### Tahap 5 — Admin

1. Tambahkan CRUD snapshot.
2. Tambahkan validasi.
3. Tambahkan publish/unpublish.
4. Tambahkan menu admin.

### Tahap 6 — Frontend

1. Buat Blade khusus.
2. Buat pie chart.
3. Buat tabel komposisi.
4. Buat menu analitik.
5. Buat filter rentang.
6. Buat line chart.
7. Buat tabel riwayat.
8. Buat empty state.

### Tahap 7 — AJAX dan aksesibilitas

1. Integrasikan initializer ke `initPublicPage`.
2. Tambahkan cleanup.
3. Tambahkan ResizeObserver.
4. Tambahkan keyboard interaction.
5. Tambahkan reduced-motion behavior.

### Tahap 8 — Test

Jalankan:

```bash
php artisan test
npm run build
```

Perbaiki seluruh error, warning penting, dan console error sebelum selesai.

---

## 27. Larangan Implementasi

- Jangan memakai angka penduduk contoh sebagai data produksi.
- Jangan menyebut semua data tahunan sebagai sensus.
- Jangan menggunakan Highcharts karena lisensi penggunaan komersialnya berbeda.
- Jangan menambahkan jQuery.
- Jangan memasang DataTables hanya untuk tabel tujuh sampai puluhan baris.
- Jangan memakai CDN untuk ECharts.
- Jangan membuat chart melalui gambar statis.
- Jangan menghitung persentase hanya di JavaScript.
- Jangan mengubah route lama tanpa backward compatibility.
- Jangan membuat handler global berulang setiap AJAX navigation.
- Jangan menggunakan ikon tiga titik sebagai satu-satunya kontrol penting.
- Jangan menyambungkan gap data tahunan secara palsu.
- Jangan mengubah layout, navbar, footer, atau desain halaman lain yang tidak terkait.

---

## 28. Referensi Teknis

Gunakan sebagai acuan:

- Dokumentasi resmi Apache ECharts:
  - Basic Pie Chart
  - Doughnut Chart
  - Line Chart
  - Axis dan Data Zoom
  - Modular Import
- BPS:
  - Metadata Sensus Penduduk 2020
  - Hasil Sensus Penduduk 2020
- Peraturan:
  - Undang-Undang Nomor 16 Tahun 1997 tentang Statistik
- Kode proyek branch `baru`:
  - `package.json`
  - `composer.json`
  - `routes/web.php`
  - `PopulationStatistics.php`
  - `PublicSiteService.php`
  - `statistic-detail.blade.php`
  - `app.js`

---

## 29. Hasil Akhir yang Diharapkan

Halaman harus terasa seperti dashboard statistik pemerintahan desa yang modern, rapi, informatif, dan terpercaya, tetapi tetap menyatu dengan identitas visual website Desa Sukomulyo.

Prioritas utama:

1. Kejelasan angka.
2. Integritas sumber data.
3. Kemudahan membandingkan laki-laki dan perempuan.
4. Kemudahan membaca tren tahun ke tahun.
5. Tampilan responsif.
6. Implementasi yang mudah dipelihara.
