<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'admin@sukomulyo.desa.id')->firstOrFail();

        $categories = collect([
            ['name' => 'Pemerintahan', 'slug' => 'pemerintahan', 'description' => 'Informasi pelayanan, kebijakan, dan tata kelola Pemerintah Desa Sukomulyo.'],
            ['name' => 'Pembangunan', 'slug' => 'pembangunan', 'description' => 'Perkembangan pembangunan sarana dan prasarana desa.'],
            ['name' => 'Kemasyarakatan', 'slug' => 'kemasyarakatan', 'description' => 'Kegiatan gotong royong, sosial, budaya, dan kebersamaan warga.'],
            ['name' => 'Kesehatan', 'slug' => 'kesehatan', 'description' => 'Informasi layanan kesehatan dan kegiatan Posyandu desa.'],
            ['name' => 'Pendidikan', 'slug' => 'pendidikan', 'description' => 'Kegiatan pendidikan, literasi, dan pengembangan generasi muda.'],
            ['name' => 'Potensi Desa', 'slug' => 'potensi-desa', 'description' => 'Kabar UMKM, pertanian, wisata, dan potensi ekonomi lokal.'],
            ['name' => 'Lingkungan', 'slug' => 'lingkungan', 'description' => 'Program kebersihan, penghijauan, dan kelestarian lingkungan desa.'],
        ])->mapWithKeys(function (array $category): array {
            $model = NewsCategory::updateOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name'], 'description' => $category['description']]
            );

            return [$category['slug'] => $model];
        });

        $articles = [
            [
                'category' => 'pemerintahan',
                'title' => 'Musyawarah Desa Tetapkan Prioritas Program Kerja 2026',
                'slug' => 'musyawarah-desa-penyusunan-program-kerja',
                'excerpt' => 'Pemerintah desa bersama BPD dan perwakilan warga menyepakati program prioritas yang transparan dan tepat sasaran.',
                'published_at' => '2026-07-25 09:00:00',
                'content' => [
                    'Pemerintah Desa Sukomulyo menyelenggarakan musyawarah desa untuk menyusun dan menetapkan prioritas program kerja tahun 2026.',
                    'Forum ini dihadiri oleh BPD, perangkat desa, ketua RT dan RW, tokoh masyarakat, kader, serta perwakilan kelompok warga.',
                    'Setiap usulan dicatat dan dikelompokkan berdasarkan tingkat kebutuhan, manfaat bagi masyarakat, serta kesiapan pelaksanaannya.',
                ],
            ],
            [
                'category' => 'pembangunan',
                'title' => 'Perbaikan Jalan Lingkungan Dusun Mulai Dilaksanakan',
                'slug' => 'perbaikan-jalan-lingkungan-dusun',
                'excerpt' => 'Peningkatan kualitas jalan lingkungan dilakukan bertahap untuk mendukung mobilitas warga dan kegiatan ekonomi desa.',
                'published_at' => '2026-07-23 08:30:00',
                'content' => [
                    'Pemerintah Desa Sukomulyo memulai pekerjaan perbaikan jalan lingkungan pada sejumlah titik yang telah ditetapkan melalui musyawarah.',
                    'Warga diimbau memperhatikan rambu pekerjaan dan menggunakan jalur alternatif selama proses perbaikan berlangsung.',
                ],
            ],
            [
                'category' => 'pendidikan',
                'title' => 'Kelas Literasi Digital Bekali Remaja Desa',
                'slug' => 'kelas-literasi-digital-remaja-desa',
                'excerpt' => 'Remaja desa belajar menggunakan media digital secara aman, produktif, dan bertanggung jawab.',
                'published_at' => '2026-07-21 13:00:00',
                'content' => [
                    'Kelas literasi digital diikuti oleh pelajar dan remaja Desa Sukomulyo dengan materi keamanan akun, etika bermedia sosial, dan pemanfaatan teknologi untuk belajar.',
                    'Kegiatan berlangsung interaktif melalui studi kasus dan praktik sederhana yang dekat dengan aktivitas peserta sehari-hari.',
                ],
            ],
            [
                'category' => 'kemasyarakatan',
                'title' => 'Kerja Bakti Warga Bersihkan Saluran Air Desa',
                'slug' => 'kerja-bakti-lingkungan-desa',
                'excerpt' => 'Warga bergotong royong membersihkan jalan, saluran air, dan fasilitas umum untuk menjaga lingkungan tetap nyaman.',
                'published_at' => '2026-07-18 06:30:00',
                'content' => [
                    'Warga dari beberapa wilayah berkumpul sejak pagi untuk membersihkan saluran air, bahu jalan, dan area fasilitas umum.',
                    'Kegiatan rutin ini menjadi upaya bersama mencegah genangan sekaligus memperkuat kebersamaan antarwarga.',
                ],
            ],
            [
                'category' => 'potensi-desa',
                'title' => 'Pelatihan Foto Produk dan Pemasaran Digital untuk UMKM',
                'slug' => 'pelatihan-pemasaran-digital-umkm',
                'excerpt' => 'Pelaku UMKM mempraktikkan foto produk, penulisan promosi, dan pengelolaan media sosial untuk memperluas pasar.',
                'published_at' => '2026-07-15 10:00:00',
                'content' => [
                    'Pelatihan pemasaran digital dirancang agar produk unggulan warga semakin mudah dikenal dan memiliki jangkauan pasar yang lebih luas.',
                    'Peserta mempraktikkan penataan produk, pengambilan foto dengan telepon seluler, serta penyusunan materi promosi sederhana.',
                ],
            ],
            [
                'category' => 'kesehatan',
                'title' => 'Posyandu Rutin Pantau Tumbuh Kembang Balita',
                'slug' => 'pelayanan-posyandu-rutin',
                'excerpt' => 'Kader Posyandu memberikan layanan penimbangan, pemantauan tumbuh kembang, dan edukasi kesehatan keluarga.',
                'published_at' => '2026-07-12 08:00:00',
                'content' => [
                    'Pelayanan Posyandu membantu keluarga memantau kesehatan ibu dan tumbuh kembang anak secara berkala.',
                    'Warga diimbau membawa buku kesehatan dan mengikuti jadwal pelayanan yang diumumkan melalui kanal informasi desa.',
                ],
            ],
            [
                'category' => 'lingkungan',
                'title' => 'Gerakan Menanam Pohon Digelar di Area Fasilitas Umum',
                'slug' => 'gerakan-menanam-pohon-fasilitas-umum',
                'excerpt' => 'Pemerintah desa dan warga menanam pohon peneduh untuk menciptakan ruang publik yang lebih hijau dan nyaman.',
                'published_at' => '2026-07-09 07:00:00',
                'content' => [
                    'Gerakan menanam pohon dilaksanakan di sekitar fasilitas umum dan ruas jalan desa yang membutuhkan tambahan peneduh.',
                    'Warga sekitar akan ikut merawat bibit agar tumbuh baik dan memberikan manfaat dalam jangka panjang.',
                ],
            ],
            [
                'category' => 'potensi-desa',
                'title' => 'Produk Olahan Warga Sukomulyo Tampil di Bazar Kecamatan',
                'slug' => 'produk-olahan-warga-bazar-kecamatan',
                'excerpt' => 'Aneka produk pangan olahan warga diperkenalkan kepada pengunjung dalam bazar UMKM tingkat kecamatan.',
                'published_at' => '2026-07-05 09:30:00',
                'content' => [
                    'Pelaku usaha Desa Sukomulyo membawa beragam produk olahan unggulan untuk dipamerkan dan dipasarkan dalam bazar kecamatan.',
                    'Partisipasi ini membuka kesempatan promosi, jejaring usaha, dan masukan langsung dari konsumen.',
                ],
            ],
            [
                'category' => 'pemerintahan',
                'title' => 'Pelayanan Administrasi Desa Kini Lebih Terjadwal',
                'slug' => 'pelayanan-administrasi-desa-lebih-terjadwal',
                'excerpt' => 'Jadwal dan alur pelayanan diperjelas agar warga lebih mudah menyiapkan dokumen yang dibutuhkan.',
                'published_at' => '2026-06-28 08:00:00',
                'content' => [
                    'Pemerintah Desa Sukomulyo memperjelas jadwal serta persyaratan pelayanan administrasi untuk membantu warga memperoleh layanan secara efektif.',
                    'Informasi persyaratan dapat dilihat melalui website desa atau ditanyakan langsung kepada petugas pelayanan.',
                ],
            ],
            [
                'category' => 'kemasyarakatan',
                'title' => 'Pentas Seni Warga Meriahkan Malam Kebersamaan',
                'slug' => 'pentas-seni-warga-malam-kebersamaan',
                'excerpt' => 'Pertunjukan seni dari berbagai kelompok warga menjadi ruang silaturahmi sekaligus pelestarian budaya lokal.',
                'published_at' => '2026-06-21 19:00:00',
                'content' => [
                    'Malam kebersamaan menampilkan pertunjukan musik, tari, dan kreasi warga dari berbagai kelompok usia.',
                    'Kegiatan berlangsung meriah dan menjadi ruang apresiasi bagi talenta lokal Desa Sukomulyo.',
                ],
            ],
        ];

        foreach ($articles as $article) {
            $content = collect($article['content'])
                ->map(fn (string $paragraph): string => '<p>'.$paragraph.'</p>')
                ->implode('');

            News::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'category_id' => $categories[$article['category']]->id,
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'content' => $content,
                    'status' => 'published',
                    'published_at' => $article['published_at'],
                    'author_id' => $author->id,
                    'seo_title' => $article['title'],
                    'seo_description' => $article['excerpt'],
                ]
            );
        }
    }
}
