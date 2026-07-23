# Acuan Editor Konten dan Sanitasi HTML

Dokumen ini menjadi acuan implementasi rich-text editor untuk CMS Desa Sukomulyo. Aturan di sini berlaku untuk konten HTML pada profil desa, berita, informasi publik, dan modul lain yang menggunakan editor WYSIWYG.

## 1. Keputusan teknologi

| Bagian | Keputusan |
|---|---|
| Editor browser | Tiptap 3 |
| Integrasi frontend | Vanilla JavaScript melalui Vite |
| Styling editor dan output | Tailwind CSS 4 dan Tailwind Typography |
| Format penyimpanan | HTML yang telah disanitasi |
| Sanitasi utama | HTML Purifier di server Laravel |
| Database | Kolom `LONGTEXT` untuk konten |
| Gambar | Dipilih dari Media Library; tidak menerima base64 |
| HTML mentah | Tidak tersedia bagi admin |

Editor hanya membantu admin menyusun konten. Editor bukan batas keamanan. Semua request dianggap tidak tepercaya dan wajib melewati sanitasi server, termasuk request yang tidak berasal dari antarmuka editor.

Referensi:

- [Tiptap Vanilla JavaScript](https://tiptap.dev/docs/editor/getting-started/install/vanilla-javascript)
- [Tiptap Persistence](https://tiptap.dev/docs/editor/core-concepts/persistence)
- [HTML Purifier](https://htmlpurifier.org/docs/)
- [OWASP Cross-Site Scripting Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)

## 2. Dependensi

Frontend:

```bash
npm install @tiptap/core @tiptap/pm @tiptap/starter-kit
npm install @tiptap/extension-link @tiptap/extension-underline
npm install -D @tailwindcss/typography
```

Tambahkan dukungan tabel hanya ketika tabel konten mulai diimplementasikan:

```bash
npm install @tiptap/extension-table
```

Backend:

```bash
composer require ezyang/htmlpurifier
```

Semua versi aktual harus dikunci melalui `package-lock.json` dan `composer.lock`. Dependensi sanitizer harus ikut diperiksa melalui `npm audit` dan `composer audit` pada CI.

## 3. Fitur editor MVP

Toolbar editor menyediakan:

- Paragraph.
- Heading level 2, 3, dan 4.
- Bold, italic, underline, dan strikethrough.
- Bullet list dan numbered list.
- Blockquote.
- Link.
- Undo dan redo.
- Tabel sederhana apabila extension tabel diaktifkan.
- Gambar yang dipilih dari Media Library.

Editor tidak menyediakan:

- Heading level 1; H1 berasal dari judul halaman.
- Editor source/raw HTML.
- Inline CSS, pilihan font, ukuran font, atau warna bebas.
- Script, iframe, embed, object, form, atau elemen interaktif.
- Upload atau paste gambar sebagai base64/data URI.
- Embed video dan konten pihak ketiga pada MVP.

Paste dari Word atau website lain harus tetap diperbolehkan untuk teks, tetapi hasilnya dibatasi oleh schema Tiptap dan kembali dibersihkan oleh server.

## 4. Allowlist HTML final

### Elemen yang diperbolehkan

```text
p
br
h2
h3
h4
strong
em
u
s
ul
ol
li
blockquote
a
table
thead
tbody
tr
th
td
figure
figcaption
img
```

Elemen yang tidak ada dalam daftar harus dibuang, bukan di-escape sebagai teks.

### Atribut yang diperbolehkan

```text
a[href|title]
img[src|alt|title|width|height]
th[colspan|rowspan|scope]
td[colspan|rowspan]
```

Ketentuan atribut:

- Semua atribut `on*`, `style`, `class`, dan `id` dibuang dari konten admin.
- Atribut `target` tidak diizinkan pada MVP; link dibuka di tab yang sama.
- `width`, `height`, `colspan`, dan `rowspan` harus berupa bilangan bulat positif dengan batas wajar.
- `scope` hanya menerima `row`, `col`, `rowgroup`, atau `colgroup`.
- `alt` wajib diisi ketika gambar disisipkan melalui Media Library.

### URL yang diperbolehkan

Link hanya menerima skema:

```text
https
http
mailto
tel
```

Ketentuan URL:

- URL relatif internal seperti `/berita/judul` diperbolehkan.
- Skema `javascript:`, `data:`, `file:`, dan skema lain di luar allowlist ditolak.
- `img[src]` hanya boleh mengarah ke file Media Library milik aplikasi, menggunakan path relatif di bawah `/storage/uploads/`.
- Hotlink gambar eksternal tidak diperbolehkan.
- URL harus dinormalisasi dan diperiksa ulang setelah proses HTML Purifier.

### Elemen yang selalu dibuang

```text
script
iframe
object
embed
form
input
textarea
select
button
style
link
meta
svg
math
```

Komentar HTML juga dibuang.

## 5. Alur data

```text
Admin mengedit konten di Tiptap
        ↓
Tiptap menghasilkan HTML ke hidden textarea
        ↓
Form Request memvalidasi keberadaan dan ukuran input
        ↓
Action memanggil HtmlSanitizer
        ↓
HTML Purifier menerapkan allowlist
        ↓
URL dan referensi Media Library dinormalisasi/divalidasi
        ↓
HTML bersih disimpan ke database
        ↓
Blade merender hanya field konten yang sudah disanitasi
```

Sanitasi dijalankan pada operasi create dan update sebelum transaksi disimpan. Jika sanitasi membuat konten kehilangan seluruh teks bermakna, request ditolak dengan pesan validasi dan konten kosong tidak disimpan.

Konten lama yang diimpor atau dimigrasikan juga harus melewati sanitizer yang sama.

## 6. Struktur implementasi Laravel

Gunakan satu service lintas modul:

```text
app/Services/HtmlSanitizer.php
```

Tanggung jawab service:

- Menginisialisasi HTML Purifier dengan konfigurasi allowlist tunggal.
- Menghapus elemen dan atribut yang tidak diizinkan.
- Memvalidasi skema link.
- Memastikan sumber gambar berasal dari Media Library internal.
- Menghasilkan HTML UTF-8 yang konsisten.
- Tidak melakukan query atau menyimpan model.

Service dipanggil dari action aplikasi, misalnya:

```text
CreateNewsAction
UpdateNewsAction
CreatePublicationAction
UpdatePublicationAction
UpdateVillageProfileAction
```

Jangan menyebarkan konfigurasi allowlist ke controller atau setiap Form Request. Controller hanya menerima request dan memanggil action; action bertanggung jawab memastikan nilai yang disimpan sudah bersih.

Kontrak service:

```php
interface HtmlSanitizer
{
    public function sanitize(string $html): string;
}
```

Implementasi konkret dapat dinamai `HtmlPurifierSanitizer`. Binding interface ke implementasi ditempatkan di service provider agar dapat diganti dan mudah diuji.

## 7. Integrasi Tiptap

Entry JavaScript admin:

```text
resources/js/admin/editor.js
```

Komponen Blade editor harus memiliki:

- Container editor.
- Hidden `textarea` dengan nama field sebenarnya, misalnya `content`.
- Toolbar dengan tombol eksplisit.
- Area error validasi.
- Dukungan initial value untuk form edit dan `old('content')` setelah validasi gagal.

Pada setiap perubahan editor, HTML Tiptap disalin ke hidden textarea. Tepat sebelum submit, nilai harus disinkronkan sekali lagi agar perubahan terakhir tidak hilang.

Editor diinisialisasi hanya pada halaman yang memiliki atribut penanda editor. Jangan memuat kode Tiptap pada seluruh halaman publik.

## 8. Media dalam konten

- Penyisipan gambar membuka Media Library dan mengembalikan media yang sudah tersimpan.
- Editor tidak menerima URL gambar bebas.
- Server memverifikasi bahwa path gambar menunjuk ke record `media` yang masih tersedia.
- File harus berupa tipe gambar yang disetujui oleh Media Library; SVG tidak diizinkan pada MVP.
- `alt_text` dari Media Library menjadi nilai awal `alt`, tetapi admin boleh menyesuaikannya sesuai konteks artikel.
- Penghapusan permanen media ditolak selama masih direferensikan oleh konten atau relasi terstruktur.
- Jangan menyimpan blob/base64 di kolom `content`.

Karena referensi gambar di dalam HTML tidak memiliki foreign key, implementasi Media Library harus memiliki pemeriksaan penggunaan konten sebelum hard delete. Pencarian path media dilakukan terhadap konten aktif dan soft-deleted.

## 9. Rendering Blade

Hanya field rich-text yang telah melewati sanitizer boleh dirender tanpa escaping:

```blade
<article class="prose prose-slate max-w-none">
    {!! $news->content !!}
</article>
```

Field biasa tetap menggunakan escaping Blade:

```blade
{{ $news->title }}
{{ $news->excerpt }}
{{ $media->caption }}
```

Jangan menggunakan `{!! !!}` untuk judul, nama, excerpt, caption, input kontak, pesan warga, atau data lain yang bukan rich-text tersanitasi.

Sanitasi saat penyimpanan adalah kontrol utama untuk konten editor. Content Security Policy dan security headers dipakai sebagai lapisan tambahan, bukan pengganti sanitasi.

## 10. Validasi input

Form Request minimal memeriksa:

- `content` wajib berupa string.
- Ukuran input mentah dibatasi; default maksimum MVP adalah 500 KB per field rich-text.
- Konten hasil sanitasi harus memiliki teks bermakna atau media valid.
- Link dan path gambar harus lolos kebijakan URL.
- Jumlah gambar dan tabel dapat dibatasi jika ditemukan masalah performa, tetapi bukan persyaratan awal MVP.

Validasi panjang dilakukan sebelum sanitasi untuk menolak payload berlebihan. Validasi konten bermakna dilakukan setelah sanitasi.

## 11. Test keamanan wajib

### Payload yang harus dibersihkan

```html
<script>alert(1)</script>
<img src="x" onerror="alert(1)">
<a href="javascript:alert(1)">Klik</a>
<p style="background:url(javascript:alert(1))">Tes</p>
<iframe src="https://example.com"></iframe>
<svg onload="alert(1)"></svg>
<form><input name="password"></form>
```

Ekspektasi:

- `script`, `iframe`, `svg`, dan `form` hilang.
- Semua event handler dan inline style hilang.
- URL `javascript:` dan sumber gambar non-Media-Library ditolak atau atributnya dibuang.
- Tidak ada JavaScript yang dijalankan ketika output dirender.

### Konten valid yang harus dipertahankan

- Paragraph dan line break.
- Heading H2–H4.
- Bold, italic, underline, dan strikethrough.
- Ordered dan unordered list.
- Blockquote.
- Link HTTP(S), email, telepon, dan link relatif internal.
- Tabel dengan header dan cell span yang valid.
- Gambar Media Library dengan alt text.
- Karakter Unicode dan bahasa Indonesia.

### Test integrasi

- Create dan update berita menyimpan hasil sanitasi, bukan input mentah.
- Validasi gagal mengembalikan konten aman ke editor.
- Preview admin dan halaman publik menghasilkan struktur yang sama.
- Konten lama berbahaya tidak menjadi aktif setelah proses edit atau migrasi.
- Request langsung tanpa JavaScript tetap melewati sanitizer.
- Field non-rich-text tetap di-escape oleh Blade.

## 12. Acceptance criteria

Implementasi dianggap selesai ketika:

- Tiptap berjalan melalui Vite tanpa CDN runtime.
- Toolbar hanya menampilkan fitur yang disetujui.
- Seluruh modul memakai service sanitizer yang sama.
- Database hanya menerima HTML hasil sanitasi untuk field rich-text.
- Semua payload XSS dalam test otomatis dinetralisasi.
- Gambar hanya dapat berasal dari Media Library internal.
- Output publik memiliki styling Tailwind Typography yang konsisten.
- `npm run build`, `composer audit`, dan `php artisan test` lulus.
- Tidak ada penggunaan raw Blade output untuk data yang belum disanitasi.

## 13. Di luar cakupan MVP

- Kolaborasi real-time.
- Comments dan tracked changes.
- AI writing assistant.
- Embed iframe, YouTube, atau media pihak ketiga.
- Custom font dan inline styling.
- Penyimpanan JSON document Tiptap sebagai format utama.
- Editor HTML mentah.
- Upload gambar langsung dari editor tanpa Media Library.

Fitur tersebut hanya ditambahkan setelah kebutuhan produk, keamanan, lisensi, dan dampak hosting dinilai kembali.
