<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Publication;
use App\Models\PublicationAttachment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'admin@sukomulyo.desa.id')->firstOrFail();
        $disk = 'public';

        $announcements = [
            [
                'title' => 'Pemberitahuan Pelaksanaan Kerja Bakti Desa Sukomulyo Bulan Juli 2026',
                'slug' => 'pemberitahuan-kerja-bakti-desa-juli-2026',
                'excerpt' => 'Warga Desa Sukomulyo diimbau mengikuti kerja bakti serentak sesuai jadwal yang telah ditetapkan.',
                'content' => '<p>Pemerintah Desa Sukomulyo mengundang seluruh warga untuk mengikuti kerja bakti serentak di lingkungan masing-masing.</p><p>Silakan unduh surat pemberitahuan untuk mengetahui jadwal, titik kumpul, dan perlengkapan yang perlu disiapkan.</p>',
                'published_at' => '2026-07-24 08:00:00',
                'document_title' => 'Surat Pemberitahuan Kerja Bakti Desa Sukomulyo',
                'file_name' => 'pemberitahuan-kerja-bakti-juli-2026.pdf',
                'download_count' => 29,
            ],
            [
                'title' => 'Jadwal Pelayanan Posyandu Desa Sukomulyo Bulan Juli 2026',
                'slug' => 'jadwal-pelayanan-posyandu-juli-2026',
                'excerpt' => 'Informasi jadwal pelayanan Posyandu bagi balita, ibu hamil, dan lansia di Desa Sukomulyo.',
                'content' => '<p>Pelayanan Posyandu dilaksanakan di setiap dusun sesuai jadwal pada lampiran pengumuman ini.</p><p>Warga diminta membawa buku KIA atau kartu pemantauan kesehatan saat datang ke lokasi pelayanan.</p>',
                'published_at' => '2026-07-17 08:00:00',
                'document_title' => 'Jadwal Pelayanan Posyandu Desa Sukomulyo',
                'file_name' => 'jadwal-posyandu-juli-2026.pdf',
                'download_count' => 68,
            ],
            [
                'title' => 'Undangan Musyawarah Desa Penyusunan RKP Desa Tahun 2027',
                'slug' => 'undangan-musyawarah-desa-rkp-2027',
                'excerpt' => 'Pemerintah desa mengundang perwakilan warga untuk menyampaikan usulan prioritas pembangunan desa.',
                'content' => '<p>Musyawarah Desa diselenggarakan untuk menghimpun aspirasi dan menyusun prioritas Rencana Kerja Pemerintah Desa tahun 2027.</p><p>Rincian waktu, tempat, serta daftar peserta dapat dilihat pada lampiran undangan.</p>',
                'published_at' => '2026-07-12 08:00:00',
                'document_title' => 'Undangan Musyawarah Desa Penyusunan RKP Desa Tahun 2027',
                'file_name' => 'undangan-musyawarah-desa-rkp-2027.pdf',
                'download_count' => 41,
            ],
        ];

        foreach ($announcements as $item) {
            $publication = Publication::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'type' => 'announcement',
                    'title' => $item['title'],
                    'excerpt' => $item['excerpt'],
                    'content' => $item['content'],
                    'status' => 'published',
                    'published_at' => $item['published_at'],
                    'author_id' => $author->id,
                    'seo_title' => $item['title'],
                    'seo_description' => $item['excerpt'],
                ],
            );

            $path = 'dokumen-publik/pengumuman/'.$item['slug'].'/'.$item['file_name'];
            $pdf = $this->makePdf($item['document_title']);
            Storage::disk($disk)->put($path, $pdf);

            $media = Media::updateOrCreate(
                ['disk' => $disk, 'storage_path' => $path],
                [
                    'original_name' => $item['file_name'],
                    'stored_name' => $item['file_name'],
                    'mime_type' => 'application/pdf',
                    'extension' => 'pdf',
                    'file_size' => strlen($pdf),
                    'uploaded_by' => $author->id,
                ],
            );

            PublicationAttachment::updateOrCreate(
                ['publication_id' => $publication->id, 'media_id' => $media->id],
                [
                    'title' => $item['document_title'],
                    'display_order' => 0,
                    'download_count' => $item['download_count'],
                ],
            );
        }
    }

    private function makePdf(string $title): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $title);
        $stream = "BT\n/F1 16 Tf\n72 720 Td\n(".$text.") Tj\nET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            "<< /Length ".strlen($stream)." >>\nstream\n".$stream."\nendstream",
        ];

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf('%010d 00000 n ', $offset)."\n";
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xrefOffset."\n%%EOF\n";
    }
}
