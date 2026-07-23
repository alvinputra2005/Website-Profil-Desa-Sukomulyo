<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Official;
use App\Models\Setting;
use App\Models\VillageProfileSection;
use App\Services\PopulationStatistics as PopulationStatisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SiteController extends Controller
{
    public function home(PopulationStatisticsService $populationStatistics): View
    {
        $populationSummary = Schema::hasTable('residents')
            ? $populationStatistics->summary()
            : ['residents' => 0, 'families' => 0, 'areas' => 0];

        return $this->render('pages.home', [
            'missions' => [
                ['icon' => 'fas fa-home', 'title' => 'Profile Desa', 'route' => 'profile-desa'],
                ['icon' => 'fas fa-chart-bar', 'title' => 'Data & Statistik', 'route' => 'data-desa-statistik'],
                ['icon' => 'fas fa-file-alt', 'title' => 'Informasi Publik', 'route' => 'informasi-publik-desa'],
                ['icon' => 'fas fa-newspaper', 'title' => 'Berita Desa', 'route' => 'berita-desa.index'],
                ['icon' => 'fas fa-map-marked-alt', 'title' => 'Peta Desa', 'route' => 'peta-desa'],
            ],
            'villageStatistics' => [
                ['icon' => 'fas fa-users', 'value' => $populationSummary['residents'], 'unit' => 'jiwa', 'label' => 'Jumlah Penduduk'],
                ['icon' => 'fas fa-home', 'value' => $populationSummary['families'], 'unit' => 'KK', 'label' => 'Kepala Keluarga'],
                ['icon' => 'fas fa-map-signs', 'value' => $populationSummary['areas'], 'unit' => 'dusun', 'label' => 'Wilayah Administratif', 'meta' => 'Data wilayah kependudukan'],
                ['icon' => 'fas fa-map', 'value' => 430, 'unit' => 'hektare', 'label' => 'Luas Wilayah'],
            ],
            'featuredPotentials' => array_slice($this->potentialsData(), 0, 2),
        ]);
    }

    public function profile(): View
    {
        return $this->render('pages.profile', ['profileSections' => Schema::hasTable('village_profile_sections') ? VillageProfileSection::whereIn('section_key', ['profile', 'history', 'vision', 'mission'])->where('status', 'published')->orderBy('display_order')->get() : collect()]);
    }

    public function statistics(PopulationStatisticsService $populationStatistics): View
    {
        $summary = Schema::hasTable('residents')
            ? $populationStatistics->summary()
            : ['residents' => 0, 'male' => 0, 'female' => 0, 'families' => 0, 'households' => 0, 'areas' => 0];
        $total = max($summary['residents'], 1);
        $occupations = Schema::hasTable('residents') ? $populationStatistics->distribution('occupation') : [];

        return $this->render('pages.statistics', [
            'statistics' => [
                ['icon' => 'fas fa-users', 'value' => number_format($summary['residents'], 0, ',', '.'), 'label' => 'Jumlah Penduduk', 'unit' => 'jiwa'],
                ['icon' => 'fas fa-home', 'value' => number_format($summary['families'], 0, ',', '.'), 'label' => 'Kepala Keluarga', 'unit' => 'KK'],
                ['icon' => 'fas fa-building', 'value' => number_format($summary['households'], 0, ',', '.'), 'label' => 'Rumah Tangga', 'unit' => 'rumah tangga'],
                ['icon' => 'fas fa-map-signs', 'value' => number_format($summary['areas'], 0, ',', '.'), 'label' => 'Wilayah Dusun', 'unit' => 'dusun'],
            ],
            'population' => [
                ['label' => 'Laki-laki', 'value' => $summary['male'], 'percentage' => round($summary['male'] / $total * 100, 2)],
                ['label' => 'Perempuan', 'value' => $summary['female'], 'percentage' => round($summary['female'] / $total * 100, 2)],
            ],
            'livelihoods' => collect($occupations)->take(6)->map(fn (array $row) => [
                'label' => $row['label'],
                'percentage' => $row['percentage'],
            ])->all(),
        ]);
    }

    public function populationReport(Request $request, PopulationStatisticsService $populationStatistics): View
    {
        $validated = validator($request->query(), [
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'month' => ['nullable', 'integer', 'between:1,12'],
        ])->validate();
        $year = (int) ($validated['year'] ?? now()->year);
        $month = (int) ($validated['month'] ?? now()->month);

        return $this->render('pages.population-report', array_merge(
            $populationStatistics->monthlyReport($year, $month),
            compact('year', 'month')
        ));
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
            'officials' => Schema::hasTable('officials') && Official::where('is_active', true)->exists() ? Official::where('is_active', true)->orderBy('display_order')->get()->map(fn ($o) => ['role' => $o->position, 'name' => $o->name])->all() : [
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
        $canPreviewDraft = auth()->check() && auth()->user()->can('manage-content');
        $article = collect($this->articles($canPreviewDraft))->firstWhere('slug', $slug);

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

        if (Schema::hasTable('galleries')) {
            $photos = Gallery::where('status', 'published')->with(['items.media', 'cover'])->latest('event_date')->get()->flatMap(function ($gallery) use ($image) {
                if ($gallery->items->isEmpty()) {
                    return [['src' => $gallery->cover?->url ?? $image, 'title' => $gallery->title, 'caption' => $gallery->description]];
                }

                return $gallery->items->map(fn ($item) => ['src' => $item->media->url, 'title' => $gallery->title, 'caption' => $item->caption ?? $gallery->description]);
            })->all();
            if ($photos !== []) {
                return $this->render('pages.gallery', compact('photos'));
            }
        }

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
        $setting = fn (string $key, string $fallback) => Schema::hasTable('settings') ? (Setting::where('key', $key)->value('value') ?? $fallback) : $fallback;

        return [
            'site' => [
                'name' => $setting('site.name', 'Desa Sukomulyo'),
                'tagline' => $setting('site.tagline', 'Website Resmi Pemerintah Desa Sukomulyo'),
                'email' => $setting('site.email', 'pemdes@sukomulyo.desa.id'),
                'phone' => $setting('site.phone', '(0000) 123 456'),
                'address' => $setting('site.address', 'Kantor Desa Sukomulyo, Indonesia'),
            ],
            'navigation' => [
                ['label' => 'Beranda', 'route' => 'beranda', 'active' => 'beranda'],
                ['label' => 'Profile Desa', 'route' => 'profile-desa', 'active' => 'profile-desa'],
                ['label' => 'Data Desa/Statistik', 'route' => 'data-desa-statistik', 'active' => 'data-desa-statistik'],
                ['label' => 'Kependudukan', 'route' => 'data-desa-statistik', 'active' => 'data-desa-statistik', 'children' => [
                    ['label' => 'Statistik Kependudukan', 'route' => 'data-desa-statistik', 'active' => 'data-desa-statistik'],
                    ['label' => 'Laporan Penduduk', 'route' => 'laporan-penduduk', 'active' => 'laporan-penduduk'],
                ]],
                ['label' => 'Informasi Publik Desa', 'route' => 'informasi-publik-desa', 'active' => 'informasi-publik-desa'],
                ['label' => 'Berita Desa', 'route' => 'berita-desa.index', 'active' => 'berita-desa.*'],
                ['label' => 'Peta Desa', 'route' => 'peta-desa', 'active' => 'peta-desa'],
            ],
            'articles' => $articles,
            'categories' => collect($articles)->unique('category_slug')->values()->all(),
            'archiveYears' => collect($articles)->pluck('year')->unique()->values()->all(),
        ];
    }

    private function articles(bool $includeUnpublished = false): array
    {
        $image = asset('assets/village-rice-fields.jpg');

        $hasArticles = Schema::hasTable('news') && ($includeUnpublished ? News::exists() : News::published()->exists());
        if ($hasArticles) {
            $query = News::with(['category', 'featuredImage']);
            if (! $includeUnpublished) {
                $query->published();
            }

            return $query->latest('published_at')->get()->map(function (News $article) use ($image) {
                $htmlContent = preg_replace('~https?://(?:localhost|127\.0\.0\.1)(?::\d+)?(/storage/)~i', '$1', $article->content);
                preg_match('~<img[^>]+src=["\']([^"\']+)["\']~i', $htmlContent, $inlineImage);

                return ['slug' => $article->slug, 'title' => $article->title, 'date' => ($article->published_at ?? $article->created_at)->translatedFormat('d F Y'), 'year' => (string) ($article->published_at ?? $article->created_at)->year, 'category' => $article->category->name, 'category_slug' => $article->category->slug, 'image' => $article->featuredImage?->url ?? ($inlineImage[1] ?? $image), 'excerpt' => $article->excerpt ?? strip_tags($htmlContent), 'content' => [strip_tags($htmlContent)], 'html_content' => $htmlContent, 'tags' => []];
            })->all();
        }

        return [
            [
                'slug' => 'musyawarah-desa-penyusunan-program-kerja',
                'title' => 'Musyawarah Desa Penyusunan Program Kerja',
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
