# Admin CMS Desa Sukomulyo

Panel admin tersedia di `/admin`. Jalankan `php artisan migrate --seed`, lalu masuk menggunakan akun awal:

- Email: `admin@sukomulyo.desa.id`
- Kata sandi: `Sukomulyo123!`

Ganti kata sandi dan kredensial seed sebelum deployment produksi. Asset admin terpisah berada pada `resources/css/admin.css` dan `resources/js/admin.js`; build dengan `npm run build`.

## Hak akses

- `super_admin`: seluruh modul, pengguna, pengaturan, redirect, dan audit.
- `admin_konten`: berita, publikasi, profil, perangkat, galeri, media, dan pesan.
- `admin_data`: statistik, IDM, peta, serta media pendukung.

Konten rich-text selalu disanitasi di server. File media maksimal 10 MB dan media yang masih direferensikan tidak dapat dihapus. Halaman publik membaca konten database yang sudah terbit dan tetap menggunakan fallback bawaan ketika database belum memiliki konten.
