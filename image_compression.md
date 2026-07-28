1. Ubah kompresi sisi browser

Jadikan kompresi browser hanya untuk mengurangi dimensi dan beban unggah, bukan menentukan kualitas akhir.

Pada:

resources/js/image-upload.js

Ubah:

const WEBP_QUALITY = 0.82;

menjadi:

const WEBP_QUALITY = 0.92;

Kemudian pertimbangkan mengubah:

const needsResize = scale < 1 || file.size > MAX_CLIENT_BYTES;

menjadi:

const needsResize = scale < 1;

Dengan demikian:

gambar di atas 1920 piksel diperkecil di peramban dengan kualitas tinggi;
gambar yang dimensinya sudah kecil dikirim tanpa dikompresi di browser;
Laravel melakukan kompresi akhir hanya satu kali;
kualitas akhir tetap konsisten pada WebP 82.

Konfigurasi server tetap menjadi pengendali utama.

2. Tambahkan ukuran menengah

Saat ini sistem hanya menghasilkan:

Main      : maksimal 1920 px
Thumbnail : maksimal 600 px

Sebaiknya tambahkan satu varian:

Medium : maksimal 1200 px, quality 80

Arsitektur akhirnya:

Varian	Dimensi maksimum	Kualitas	Penggunaan
Thumbnail	600 px	76	daftar berita, galeri kecil, kartu
Medium	1200 px	80	berita unggulan, galeri sedang, perangkat desa
Main	1920 px	82	hero, isi artikel, lightbox
Poster khusus	2560 px	88–90	infografik atau gambar berisi tulisan

Dengan ukuran menengah, halaman tidak perlu memuat gambar 1920 piksel hanya untuk area yang lebarnya sekitar 700–1000 piksel.

3. Gunakan gambar sesuai ukuran tampilannya

Kesalahan yang paling sering menimbulkan gambar buram bukan kualitas kompresi, melainkan thumbnail 600 piksel ditampilkan terlalu besar.

Gunakan pembagian berikut:

Kartu berita kecil        → thumbnail
Daftar berita lebar       → medium
Foto perangkat desa       → thumbnail atau medium
Hero halaman              → main
Isi artikel               → medium atau main
Lightbox/perbesar gambar   → main

Jangan menggunakan thumbnail_url untuk:

hero
gambar artikel selebar halaman
lightbox
gambar yang bisa diperbesar
4. Terapkan srcset

Peramban sebaiknya memilih ukuran gambar berdasarkan ukuran layar.

Contoh:

<img
    src="{{ $media->thumbnail_url }}"
    srcset="
        {{ $media->thumbnail_url }} 600w,
        {{ $media->medium_url }} 1200w,
        {{ $media->url }} 1920w
    "
    sizes="(max-width: 768px) 100vw, 1200px"
    width="{{ $media->width }}"
    height="{{ $media->height }}"
    loading="lazy"
    decoding="async"
    alt="{{ $media->alt_text }}"
>

Dampaknya:

ponsel tidak mengunduh gambar 1920 piksel bila tidak diperlukan;
layar besar memperoleh gambar yang lebih tajam;
penggunaan data lebih hemat;
halaman lebih cepat;
perubahan tata letak saat gambar dimuat dapat dikurangi dengan atribut width dan height.

Untuk gambar paling atas atau hero, jangan gunakan loading="lazy" karena gambar tersebut perlu segera dimuat.

5. Bedakan foto dengan poster atau gambar bertulisan

WebP quality 82 cocok untuk:

foto kegiatan
foto perangkat desa
foto lingkungan
dokumentasi pembangunan
galeri

Namun, gambar berikut sebaiknya tidak menggunakan aturan yang sama:

poster
infografik
peta
diagram
tangkapan layar
gambar dengan teks kecil
logo

Gunakan profil pengolahan yang berbeda:

Foto biasa
Main 1920 px
Quality 82
Poster atau infografik
Main 2560 px
Quality 88–90
Logo dan gambar transparan
WebP lossless atau PNG
Tidak diperkecil secara agresif

Struktur ImageProcessor dapat dikembangkan menjadi:

$this->images->store(
    file: $file,
    directory: 'berita',
    disk: $disk,
    preset: 'photo',
);

Pilihan preset:

photo
poster
logo
avatar

Jangan menaikkan seluruh gambar menjadi 2560 piksel dan kualitas 90 karena akan memperbesar penyimpanan serta waktu muat tanpa manfaat pada kartu atau thumbnail.

6. Tetap pertahankan batas unggahan

Konfigurasi sekarang membatasi:

Ukuran berkas : 5 MB
Dimensi        : 4000 × 4000 px
Format         : JPG, JPEG, PNG, WebP

Untuk shared hosting Hostinger, batas tersebut cukup aman karena pemrosesan gambar besar oleh PHP GD dapat menggunakan memori cukup tinggi.

Tidak perlu menaikkan batas dimensi kecuali banyak foto kamera gagal diunggah. Sistem browser sudah berusaha mengecilkan gambar sebelum sampai ke server.

7. Tambahkan cache panjang untuk gambar R2

Karena nama gambar menggunakan UUID, setiap gambar baru memperoleh nama baru.

Objek di R2 dapat diberi header:

Cache-Control: public, max-age=31536000, immutable
Content-Type: image/webp

Cloudflare kemudian dapat menyimpan gambar dalam cache selama satu tahun. Ini aman selama gambar yang diperbarui selalu memperoleh nama UUID baru.

Cloudflare membantu mempercepat pengiriman gambar, tetapi tidak dapat mengembalikan detail gambar yang sudah hilang akibat kompresi. Karena itu, kualitas tetap harus dijaga pada tahap pengolahan Laravel.

8. Pantau ukuran hasil

Gunakan sasaran praktis, bukan persentase kompresi tetap:

Varian	Ukuran yang umumnya baik
Thumbnail 600 px	30–120 KB
Medium 1200 px	80–300 KB
Main 1920 px	150–600 KB
Poster/infografik	dapat lebih besar sesuai detail

Kode sekarang sebenarnya sudah mencatat ukuran sumber, ukuran hasil, dimensi, dan waktu pemrosesan, tetapi pencatatan hanya aktif ketika APP_DEBUG=true.

Pada produksi, sebaiknya ukuran hasil disimpan pada tabel media dan ditampilkan di panel admin, misalnya:

Sumber     : 4,8 MB
Hasil main : 326 KB
Thumbnail  : 61 KB
Pengurangan: 93,4%
Konfigurasi final yang disarankan
Browser
├── Maksimal 1920 px
├── Quality 92
└── Hanya memproses gambar yang dimensinya terlalu besar

Laravel
├── Thumbnail 600 px / quality 76
├── Medium 1200 px / quality 80
├── Main 1920 px / quality 82
├── Poster 2560 px / quality 88–90
└── Logo menggunakan lossless

Tampilan
├── Kartu memakai thumbnail
├── Area sedang memakai medium
├── Hero dan lightbox memakai main
├── Gunakan srcset
└── Lazy loading kecuali gambar utama

Cloudflare R2
├── Custom domain media
├── Cache panjang
└── UUID untuk setiap versi gambar

Prioritas pertama adalah menghindari kompresi kualitas 82 dua kali, kemudian menambahkan varian 1200 piksel dan memastikan setiap halaman memakai ukuran gambar yang benar.