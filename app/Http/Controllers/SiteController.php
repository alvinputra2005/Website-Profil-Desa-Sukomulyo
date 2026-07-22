<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SiteController extends Controller
{
    public function home(): View
    {
        return $this->render('pages.home', [
            'missions' => [
                ['icon' => 'fas fa-home', 'title' => 'Profile Desa', 'route' => 'profile-desa'],
                ['icon' => 'fas fa-chart-bar', 'title' => 'Data & Statistik', 'route' => 'data-desa-statistik'],
                ['icon' => 'fas fa-file-alt', 'title' => 'Informasi Publik', 'route' => 'informasi-publik-desa'],
                ['icon' => 'fas fa-newspaper', 'title' => 'Berita Desa', 'route' => 'berita-desa.index'],
                ['icon' => 'fas fa-map-marked-alt', 'title' => 'Peta Desa', 'route' => 'peta-desa'],
            ],
            'villageStatistics' => [
                ['icon' => 'fas fa-users', 'value' => 2150, 'unit' => 'jiwa', 'label' => 'Jumlah Penduduk'],
                ['icon' => 'fas fa-home', 'value' => 720, 'unit' => 'KK', 'label' => 'Kepala Keluarga'],
                ['icon' => 'fas fa-graduation-cap', 'value' => 1680, 'unit' => 'orang', 'label' => 'Data Pendidikan', 'meta'],
                ['icon' => 'fas fa-briefcase', 'value' => 1240, 'unit' => 'orang', 'label' => 'Data Pekerjaan', 'meta'],
            ],
            'apbdes' => [
                'year' => 2026,
                'panels' => [
                    [
                        'title' => 'Pendapatan APBDes 2026',
                        'icon' => 'fas fa-wallet',
                        'total' => 2485000000,
                        'items' => [
                            ['label' => 'Dana Desa', 'value' => 1150000000],
                            ['label' => 'Alokasi Dana Desa', 'value' => 850000000],
                            ['label' => 'Bagi Hasil Pajak dan Retribusi', 'value' => 310000000],
                            ['label' => 'Pendapatan Asli Desa', 'value' => 175000000],
                        ],
                    ],
                    [
                        'title' => 'Belanja APBDes 2026',
                        'icon' => 'fas fa-file-invoice-dollar',
                        'total' => 2350000000,
                        'items' => [
                            ['label' => 'Penyelenggaraan Pemerintahan', 'value' => 720000000],
                            ['label' => 'Pelaksanaan Pembangunan', 'value' => 930000000],
                            ['label' => 'Pembinaan Kemasyarakatan', 'value' => 270000000],
                            ['label' => 'Pemberdayaan Masyarakat', 'value' => 430000000],
                        ],
                    ],
                    [
                        'title' => 'Realisasi APBDes 2026',
                        'icon' => 'fas fa-chart-line',
                        'total' => 1739000000,
                        'items' => [
                            ['label' => 'Penyelenggaraan Pemerintahan', 'value' => 520000000, 'percentage' => 72],
                            ['label' => 'Pelaksanaan Pembangunan', 'value' => 690000000, 'percentage' => 74],
                            ['label' => 'Pembinaan Kemasyarakatan', 'value' => 194000000, 'percentage' => 72],
                            ['label' => 'Pemberdayaan Masyarakat', 'value' => 335000000, 'percentage' => 78],
                        ],
                    ],
                ],
            ],
            'featuredPotentials' => array_slice($this->potentialsData(), 0, 2),
        ]);
    }

    public function profile(): View
    {
        return $this->render('pages.profile');
    }

    public function statistics(): View
    {
        return $this->render('pages.statistics', [
            'statistics' => [
                ['icon' => 'fas fa-users', 'value' => '2.150', 'label' => 'Jumlah Penduduk', 'unit' => 'jiwa'],
                ['icon' => 'fas fa-home', 'value' => '720', 'label' => 'Kepala Keluarga', 'unit' => 'KK'],
                ['icon' => 'fas fa-graduation-cap', 'value' => '1.680', 'label' => 'Data Pendidikan', 'unit' => 'orang'],
                ['icon' => 'fas fa-briefcase', 'value' => '1.240', 'label' => 'Data Pekerjaan', 'unit' => 'orang'],
            ],
            'population' => [
                ['label' => 'Laki-laki', 'value' => 1085, 'percentage' => 50.5],
                ['label' => 'Perempuan', 'value' => 1065, 'percentage' => 49.5],
            ],
            'livelihoods' => [
                ['label' => 'Pertanian dan Perkebunan', 'percentage' => 48],
                ['label' => 'Perdagangan dan UMKM', 'percentage' => 24],
                ['label' => 'Jasa dan Pegawai', 'percentage' => 18],
                ['label' => 'Lainnya', 'percentage' => 10],
            ],
        ]);
    }

    public function publicInformation(): View
    {
        return $this->render('pages.public-information', [
            'documents' => [
                ['icon' => 'fas fa-file-pdf', 'title' => 'APBDes Desa Sukomulyo', 'category' => 'Keuangan Desa', 'year' => '2026'],
                ['icon' => 'fas fa-file-alt', 'title' => 'Rencana Kerja Pemerintah Desa', 'category' => 'Perencanaan', 'year' => '2026'],
                ['icon' => 'fas fa-clipboard-list', 'title' => 'Laporan Penyelenggaraan Pemerintahan Desa', 'category' => 'Laporan', 'year' => '2025'],
                ['icon' => 'fas fa-bullhorn', 'title' => 'Standar Pelayanan Publik Desa', 'category' => 'Pelayanan', 'year' => '2026'],
            ],
        ]);
    }

    public function map(): View
    {
        return $this->render('pages.map');
    }

    public function government(): View
    {
        return $this->render('pages.government', [
            'officials' => [
                ['role' => 'Kepala Desa', 'name' => 'Nama Kepala Desa'],
                ['role' => 'Sekretaris Desa', 'name' => 'Nama Sekretaris Desa'],
                ['role' => 'Kaur Tata Usaha dan Umum', 'name' => 'Nama Perangkat Desa'],
                ['role' => 'Kaur Keuangan', 'name' => 'Nama Perangkat Desa'],
                ['role' => 'Kasi Pemerintahan', 'name' => 'Nama Perangkat Desa'],
                ['role' => 'Kasi Kesejahteraan', 'name' => 'Nama Perangkat Desa'],
            ],
        ]);
    }

    public function potentials(): View
    {
        return $this->render('pages.potentials', ['potentials' => $this->potentialsData()]);
    }

    public function news(): View
    {
        return $this->render('news.index', [
            'heading' => 'Berita Desa',
            'description' => 'Informasi terbaru mengenai kegiatan dan perkembangan Desa Sukomulyo.',
            'visibleArticles' => $this->articles(),
        ]);
    }

    public function article(string $slug): View|Response
    {
        $article = collect($this->articles())->firstWhere('slug', $slug);

        if (! $article) {
            return $this->notFound();
        }

        return $this->render('news.show', ['article' => $article]);
    }

    public function category(string $category): View|Response
    {
        $articles = collect($this->articles())
            ->filter(fn (array $article) => $article['category_slug'] === $category)
            ->values()
            ->all();

        if ($articles === []) {
            return $this->notFound();
        }

        return $this->render('news.index', [
            'heading' => 'Kategori: '.$articles[0]['category'],
            'description' => 'Kumpulan berita dalam kategori '.$articles[0]['category'].'.',
            'visibleArticles' => $articles,
        ]);
    }

    public function archive(?string $year = null): View
    {
        $year ??= collect($this->articles())->max('year');
        $articles = collect($this->articles())->where('year', $year)->values()->all();

        return $this->render('news.index', [
            'heading' => 'Arsip Berita '.$year,
            'description' => 'Dokumentasi berita dan kegiatan Desa Sukomulyo pada tahun '.$year.'.',
            'visibleArticles' => $articles,
        ]);
    }

    public function search(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $needle = mb_strtolower($query);
        $results = $query === '' ? [] : collect($this->articles())
            ->filter(fn (array $article) => str_contains(
                mb_strtolower($article['title'].' '.$article['excerpt'].' '.$article['category']),
                $needle
            ))
            ->values()
            ->all();

        return $this->render('news.search', compact('query', 'results'));
    }

    public function gallery(): View
    {
        $image = asset('assets/village-rice-fields.jpg');

        return $this->render('pages.gallery', [
            'photos' => [
                ['src' => $image, 'title' => 'Musyawarah Desa', 'caption' => 'Warga bermusyawarah untuk menyusun program desa.'],
                ['src' => $image, 'title' => 'Kerja Bakti Warga', 'caption' => 'Gotong royong menjaga lingkungan tetap bersih.'],
                ['src' => $image, 'title' => 'Pelatihan UMKM', 'caption' => 'Peningkatan kapasitas pelaku usaha lokal.'],
                ['src' => $image, 'title' => 'Kegiatan Posyandu', 'caption' => 'Pelayanan kesehatan rutin untuk ibu dan anak.'],
                ['src' => $image, 'title' => 'Panen Bersama', 'caption' => 'Dokumentasi potensi pertanian Desa Sukomulyo.'],
                ['src' => $image, 'title' => 'Pentas Seni Desa', 'caption' => 'Ruang ekspresi seni dan budaya masyarakat.'],
            ],
        ]);
    }

    public function contact(): View
    {
        return $this->render('pages.contact');
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $message = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:25'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        ContactMessage::create($message);

        return back()->with('success', 'Pesan Anda sudah diterima. Pemerintah desa akan segera menindaklanjuti.');
    }

    public function notFound(): Response
    {
        view()->share($this->shared());

        return response()->view('errors.404', status: 404);
    }

    private function render(string $view, array $data = []): View
    {
        view()->share($this->shared());

        return view($view, $data);
    }

    private function shared(): array
    {
        $articles = $this->articles();

        return [
            'site' => [
                'name' => 'Desa Sukomulyo',
                'tagline' => 'Website Resmi Pemerintah Desa Sukomulyo',
                'email' => 'pemdes@sukomulyo.desa.id',
                'phone' => '(0000) 123 456',
                'address' => 'Kantor Desa Sukomulyo, Indonesia',
            ],
            'navigation' => [
                ['label' => 'Beranda', 'route' => 'beranda', 'active' => 'beranda'],
                ['label' => 'Profile Desa', 'route' => 'profile-desa', 'active' => 'profile-desa'],
                ['label' => 'Data Desa/Statistik', 'route' => 'data-desa-statistik', 'active' => 'data-desa-statistik'],
                ['label' => 'Informasi Publik Desa', 'route' => 'informasi-publik-desa', 'active' => 'informasi-publik-desa'],
                ['label' => 'Berita Desa', 'route' => 'berita-desa.index', 'active' => 'berita-desa.*'],
                ['label' => 'Peta Desa', 'route' => 'peta-desa', 'active' => 'peta-desa'],
            ],
            'articles' => $articles,
            'categories' => collect($articles)->unique('category_slug')->values()->all(),
            'archiveYears' => collect($articles)->pluck('year')->unique()->values()->all(),
        ];
    }

    private function articles(): array
    {
        $image = asset('assets/village-rice-fields.jpg');

        return [
            [
                'slug' => 'musyawarah-desa-penyusunan-program-kerja',
                'title' => 'Musyawarah Desa Penyusunan Program',
                'date' => '18 Juli 2026',
                'year' => '2026',
                'category' => 'Pemerintahan',
                'category_slug' => 'pemerintahan',
                'image' => $image,
                'excerpt' => 'Pemerintah desa bersama warga membahas prioritas program pembangunan yang transparan dan tepat sasaran.',
                'content' => [
                    'Musyawarah desa menjadi ruang bersama bagi pemerintah dan masyarakat untuk menentukan arah pembangunan Desa Sukomulyo.',
                    'Usulan warga dihimpun, dikelompokkan berdasarkan kebutuhan, lalu disusun menjadi program prioritas yang dapat dipantau bersama.',
                ],
                'tags' => ['musyawarah', 'pemerintahan', 'pembangunan'],
            ],
            [
                'slug' => 'kerja-bakti-lingkungan-desa',
                'title' => 'Kerja Bakti Menjaga Lingkungan Desa',
                'date' => '14 Juli 2026',
                'year' => '2026',
                'category' => 'Kemasyarakatan',
                'category_slug' => 'kemasyarakatan',
                'image' => $image,
                'excerpt' => 'Warga bergotong royong membersihkan jalan, saluran air, dan fasilitas umum di lingkungan desa.',
                'content' => [
                    'Kegiatan kerja bakti dilaksanakan secara berkala untuk menjaga kebersihan sekaligus memperkuat kebersamaan antarwarga.',
                    'Pemerintah desa mengajak seluruh masyarakat untuk menjaga hasil kerja bersama dan melaporkan fasilitas yang membutuhkan perbaikan.',
                ],
                'tags' => ['gotong royong', 'lingkungan'],
            ],
            [
                'slug' => 'pelatihan-pemasaran-digital-umkm',
                'title' => 'Pelatihan Pemasaran Digital untuk UMKM',
                'date' => '9 Juli 2026',
                'year' => '2026',
                'category' => 'Potensi Desa',
                'category_slug' => 'potensi-desa',
                'image' => $image,
                'excerpt' => 'Pelaku UMKM desa belajar memperluas pasar melalui foto produk, media sosial, dan pencatatan usaha sederhana.',
                'content' => [
                    'Pelatihan ini dirancang agar produk unggulan warga semakin mudah dikenal dan memiliki jangkauan pasar yang lebih luas.',
                    'Peserta mempraktikkan pembuatan materi promosi serta menyusun langkah pemasaran yang sesuai dengan kapasitas usahanya.',
                ],
                'tags' => ['UMKM', 'pelatihan', 'ekonomi'],
            ],
            [
                'slug' => 'pelayanan-posyandu-rutin',
                'title' => 'Pelayanan Posyandu Rutin untuk Ibu dan Anak',
                'date' => '3 Juli 2025',
                'year' => '2025',
                'category' => 'Kesehatan',
                'category_slug' => 'kesehatan',
                'image' => $image,
                'excerpt' => 'Kader Posyandu memberikan layanan penimbangan, pemantauan tumbuh kembang, dan edukasi kesehatan.',
                'content' => [
                    'Pelayanan Posyandu membantu keluarga memantau kesehatan ibu dan tumbuh kembang anak secara rutin.',
                    'Warga diimbau membawa buku kesehatan dan mengikuti jadwal pelayanan yang diumumkan melalui kanal informasi desa.',
                ],
                'tags' => ['posyandu', 'kesehatan'],
            ],
        ];
    }

    private function potentialsData(): array
    {
        $image = asset('assets/village-rice-fields.jpg');

        return [
            ['title' => 'Pertanian Produktif', 'description' => 'Lahan pertanian menjadi penggerak ekonomi dan sumber pangan masyarakat.', 'image' => $image, 'icon' => 'fas fa-seedling'],
            ['title' => 'UMKM Lokal', 'description' => 'Produk olahan dan kerajinan warga memiliki peluang pasar yang terus berkembang.', 'image' => $image, 'icon' => 'fas fa-store'],
            ['title' => 'Seni dan Budaya', 'description' => 'Tradisi lokal terus dirawat melalui kegiatan dan partisipasi lintas generasi.', 'image' => $image, 'icon' => 'fas fa-drum'],
            ['title' => 'Wisata Desa', 'description' => 'Lingkungan dan kehidupan desa menawarkan pengalaman wisata berbasis masyarakat.', 'image' => $image, 'icon' => 'fas fa-map-marked-alt'],
        ];
    }
}
