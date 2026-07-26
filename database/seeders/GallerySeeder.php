<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@sukomulyo.desa.id')->first()
            ?? User::query()->first();

        if (! $admin) {
            throw new RuntimeException('Buat pengguna admin terlebih dahulu sebelum menjalankan GallerySeeder.');
        }

        $source = public_path('assets/village-rice-fields.jpg');

        if (! is_file($source)) {
            throw new RuntimeException('Aset galeri tidak ditemukan: '.$source);
        }

        $photos = [
            ['slug' => 'musyawarah-desa', 'title' => 'Musyawarah Desa', 'caption' => 'Warga bermusyawarah untuk menyusun program desa.'],
            ['slug' => 'kerja-bakti-warga', 'title' => 'Kerja Bakti Warga', 'caption' => 'Gotong royong menjaga lingkungan tetap bersih.'],
            ['slug' => 'pelatihan-umkm', 'title' => 'Pelatihan UMKM', 'caption' => 'Peningkatan kapasitas pelaku usaha lokal.'],
            ['slug' => 'kegiatan-posyandu', 'title' => 'Kegiatan Posyandu', 'caption' => 'Pelayanan kesehatan rutin untuk ibu dan anak.'],
            ['slug' => 'panen-bersama', 'title' => 'Panen Bersama', 'caption' => 'Dokumentasi potensi pertanian Desa Sukomulyo.'],
            ['slug' => 'pentas-seni-desa', 'title' => 'Pentas Seni Desa', 'caption' => 'Ruang ekspresi seni dan budaya masyarakat.'],
            ['slug' => 'festival-desa', 'title' => 'Festival Desa', 'caption' => 'Perayaan kebersamaan warga dan kekayaan budaya desa.'],
            ['slug' => 'pelayanan-administrasi', 'title' => 'Pelayanan Administrasi Desa', 'caption' => 'Pelayanan publik yang mudah dan dekat dengan masyarakat.'],
            ['slug' => 'senam-bersama', 'title' => 'Senam Bersama Warga', 'caption' => 'Kegiatan rutin untuk menjaga kesehatan dan kebersamaan warga.'],
            ['slug' => 'pembinaan-remaja', 'title' => 'Pembinaan Remaja Desa', 'caption' => 'Pengembangan kreativitas dan karakter generasi muda Sukomulyo.'],
            ['slug' => 'penghijauan-desa', 'title' => 'Penghijauan Lingkungan Desa', 'caption' => 'Warga menanam pohon untuk lingkungan yang lebih asri.'],
            ['slug' => 'bazar-produk-lokal', 'title' => 'Bazar Produk Lokal', 'caption' => 'Promosi hasil karya dan produk unggulan masyarakat desa.'],
        ];

        $disk = Storage::disk('public');
        $gallery = Gallery::withTrashed()->firstOrNew(['slug' => 'kegiatan-desa']);
        $gallery->title = 'Kegiatan Desa';
        $gallery->description = 'Dokumentasi kegiatan warga Desa Sukomulyo.';
        $gallery->event_date = now()->toDateString();
        $gallery->status = 'published';
        $gallery->created_by = $admin->id;
        $gallery->deleted_at = null;
        $gallery->save();

        foreach ($photos as $index => $photo) {
            $path = 'galeri/kegiatan-desa/'.$photo['slug'].'.jpg';

            if (! $disk->exists($path)) {
                $disk->put($path, file_get_contents($source));
            }

            $media = Media::withTrashed()->firstOrNew([
                'disk' => 'public',
                'storage_path' => $path,
            ]);
            $media->original_name = $photo['slug'].'.jpg';
            $media->stored_name = basename($path);
            $media->mime_type = 'image/jpeg';
            $media->extension = 'jpg';
            $media->file_size = $disk->size($path);
            $media->alt_text = $photo['title'];
            $media->caption = $photo['caption'];
            $media->uploaded_by = $admin->id;
            $media->deleted_at = null;
            $media->save();

            GalleryItem::updateOrCreate(
                ['gallery_id' => $gallery->id, 'media_id' => $media->id],
                ['caption' => $photo['caption'], 'display_order' => $index],
            );
        }
    }
}
