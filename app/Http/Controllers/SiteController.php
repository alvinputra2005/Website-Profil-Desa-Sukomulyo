<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Gallery;
use App\Models\MapLayer;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Official;
use App\Models\Setting;
use App\Models\VillageProfileSection;
use App\Services\PopulationStatistics as PopulationStatisticsService;
use App\Services\SiteCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SiteController extends Controller
{
    public function __construct(private readonly SiteCache $cache) {}

    public function home(PopulationStatisticsService $populationStatistics): View
    {
        $populationSummary = $this->cache->remember(
            SiteCache::HOME_STATISTICS,
            SiteCache::TEN_MINUTES,
            fn () => Schema::hasTable('residents')
                ? $populationStatistics->summary()
                : ['residents' => 0, 'male' => 0, 'female' => 0, 'families' => 0, 'households' => 0, 'areas' => 0]
        );

        return $this->render('pages.home', [
            'villageStatistics' => [
                ['icon' => 'fas fa-users', 'value' => $populationSummary['residents'], 'unit' => 'jiwa', 'label' => 'Jumlah Penduduk'],
                ['icon' => 'fas fa-home', 'value' => $populationSummary['families'], 'unit' => 'KK', 'label' => 'Kepala Keluarga'],
                ['icon' => 'fas fa-map-signs', 'value' => $populationSummary['areas'], 'unit' => 'dusun', 'label' => 'Wilayah Administratif', 'meta' => 'Data wilayah kependudukan'],
                ['icon' => 'fas fa-map', 'value' => 430, 'unit' => 'hektare', 'label' => 'Luas Wilayah'],
            ],
            'populationByGender' => [
                ['label' => 'Laki-laki', 'value' => $populationSummary['male'], 'image' => 'assets/male-resident-avatar.jpg'],
                ['label' => 'Perempuan', 'value' => $populationSummary['female'], 'image' => 'assets/female-resident-avatar.jpg'],
            ],
            'apbdes' => [
                'year' => 2026,
                'panels' => [
                    [
                        'title' => 'Pendapatan APBDes 2026',
                        'total' => 2485000000,
                        'total_label' => 'Total Pendapatan APBDes 2026',
                        'total_percentage' => 100,
                        'items' => [
                            ['label' => 'Dana Desa', 'value' => 1150000000],
                            ['label' => 'Alokasi Dana Desa', 'value' => 850000000],
                            ['label' => 'Bagi Hasil Pajak dan Retribusi', 'value' => 310000000],
                            ['label' => 'Pendapatan Asli Desa', 'value' => 175000000],
                        ],
                    ],
                    [
                        'title' => 'Belanja APBDes 2026',
                        'total' => 2350000000,
                        'total_label' => 'Total Penggunaan Belanja APBDes 2026',
                        'total_percentage' => 95,
                        'items' => [
                            ['label' => 'Penyelenggaraan Pemerintahan', 'value' => 720000000],
                            ['label' => 'Pelaksanaan Pembangunan', 'value' => 930000000],
                            ['label' => 'Pembinaan Kemasyarakatan', 'value' => 270000000],
                            ['label' => 'Pemberdayaan Masyarakat', 'value' => 430000000],
                        ],
                    ],
                    [
                        'title' => 'Realisasi APBDes 2026',
                        'total' => 1739000000,
                        'total_label' => 'Total Realisasi APBDes 2026',
                        'total_percentage' => 74,
                        'items' => [
                            ['label' => 'Penyelenggaraan Pemerintahan', 'value' => 520000000, 'percentage' => 72],
                            ['label' => 'Pelaksanaan Pembangunan', 'value' => 690000000, 'percentage' => 74],
                            ['label' => 'Pembinaan Kemasyarakatan', 'value' => 194000000, 'percentage' => 72],
                            ['label' => 'Pemberdayaan Masyarakat', 'value' => 335000000, 'percentage' => 78],
                        ],
                    ],
                ],
            ],
            'galleryPhotos' => array_slice($this->galleryPhotos(), 0, 5),
        ]);
    }

    public function profile(): View
    {
        $profile = $this->cache->remember(
            SiteCache::PROFILE,
            SiteCache::ONE_HOUR,
            fn () => [
                'profileSections' => Schema::hasTable('village_profile_sections')
                    ? VillageProfileSection::with('image')->whereIn('section_key', ['profile', 'history', 'vision', 'mission'])->where('status', 'published')->orderBy('display_order')->get()
                    : collect(),
                'identityGroups' => $this->villageIdentity(),
            ]
        );

        return $this->render('pages.profile', $profile);
    }

    public function statistics(PopulationStatisticsService $populationStatistics): View
    {
        $statistics = $this->cache->remember(
            SiteCache::PUBLIC_STATISTICS,
            SiteCache::TEN_MINUTES,
            function () use ($populationStatistics): array {
                $summary = Schema::hasTable('residents')
                    ? $populationStatistics->summary()
                    : ['residents' => 0, 'male' => 0, 'female' => 0, 'families' => 0, 'households' => 0, 'areas' => 0];
                $total = max($summary['residents'], 1);
                $occupations = Schema::hasTable('residents') ? $populationStatistics->distribution('occupation') : [];

                return [
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
                ];
            }
        );

        return $this->render('pages.statistics', $statistics);
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

    public function budgetHistory(): View
    {
        return $this->render('pages.budget-history', [
            'budgetHistory' => [
                ['year' => 2026, 'income' => 2485000000, 'spending' => 2350000000, 'realization' => 1739000000, 'percentage' => 74],
                ['year' => 2025, 'income' => 2360000000, 'spending' => 2240000000, 'realization' => 1859200000, 'percentage' => 83],
                ['year' => 2024, 'income' => 2225000000, 'spending' => 2100000000, 'realization' => 1743000000, 'percentage' => 83],
                ['year' => 2023, 'income' => 2080000000, 'spending' => 1980000000, 'realization' => 1623600000, 'percentage' => 82],
                ['year' => 2022, 'income' => 1950000000, 'spending' => 1860000000, 'realization' => 1488000000, 'percentage' => 80],
                ['year' => 2021, 'income' => 1820000000, 'spending' => 1740000000, 'realization' => 1357200000, 'percentage' => 78],
                ['year' => 2020, 'income' => 1690000000, 'spending' => 1610000000, 'realization' => 1207500000, 'percentage' => 75],
                ['year' => 2019, 'income' => 1560000000, 'spending' => 1480000000, 'realization' => 1213600000, 'percentage' => 82],
                ['year' => 2018, 'income' => 1420000000, 'spending' => 1360000000, 'realization' => 1074400000, 'percentage' => 79],
                ['year' => 2017, 'income' => 1300000000, 'spending' => 1240000000, 'realization' => 954800000, 'percentage' => 77],
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

    public function mapGeoJson(): JsonResponse
    {
        $geoJson = $this->cache->remember(
            SiteCache::MAP_GEOJSON,
            SiteCache::THIRTY_MINUTES,
            function (): array {
                if (! Schema::hasTable('map_layers') || ! Schema::hasTable('map_features')) {
                    return ['type' => 'FeatureCollection', 'features' => []];
                }

                $layers = MapLayer::query()
                    ->where('is_visible', true)
                    ->with(['features' => fn ($query) => $query
                        ->where('is_visible', true)
                        ->with('photo')
                        ->orderBy('id')])
                    ->orderBy('display_order')
                    ->get();

                return [
                    'type' => 'FeatureCollection',
                    'features' => $layers->flatMap(fn (MapLayer $layer) => $layer->features->map(
                        fn ($feature) => [
                            'type' => 'Feature',
                            'id' => $feature->id,
                            'geometry' => $feature->geometry_json,
                            'properties' => array_merge($feature->properties_json ?? [], [
                                'name' => $feature->name,
                                'description' => $feature->description,
                                'photo_url' => $feature->photo?->url,
                                'layer' => [
                                    'id' => $layer->id,
                                    'name' => $layer->name,
                                    'slug' => $layer->slug,
                                    'style' => $layer->style_json ?? [],
                                ],
                            ]),
                        ]
                    ))->values()->all(),
                ];
            }
        );

        return response()
            ->json($geoJson)
            ->header('Cache-Control', 'public, max-age='.SiteCache::THIRTY_MINUTES);
    }

    public function government(): View
    {
        $officials = $this->cache->remember(
            SiteCache::OFFICIALS,
            SiteCache::ONE_HOUR,
            fn () => Schema::hasTable('officials') && Official::where('is_active', true)->exists()
                ? Official::with('photo')->where('is_active', true)->orderBy('display_order')->get()->map(fn ($o) => ['role' => $o->position_label, 'name' => $o->full_name, 'photo' => $o->photo?->url, 'photo_alt' => $o->photo?->alt_text])->all()
                : [
                    ['role' => 'Kepala Desa', 'name' => 'Nama Kepala Desa', 'photo' => null],
                    ['role' => 'Sekretaris Desa', 'name' => 'Nama Sekretaris Desa', 'photo' => null],
                    ['role' => 'Kaur Tata Usaha dan Umum', 'name' => 'Nama Perangkat Desa', 'photo' => null],
                    ['role' => 'Kaur Keuangan', 'name' => 'Nama Perangkat Desa', 'photo' => null],
                    ['role' => 'Kasi Pemerintahan', 'name' => 'Nama Perangkat Desa', 'photo' => null],
                    ['role' => 'Kasi Kesejahteraan', 'name' => 'Nama Perangkat Desa', 'photo' => null],
                ]
        );

        return $this->render('pages.government', [
            'officials' => $officials,
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
        $article = $this->articleData($slug, $canPreviewDraft);

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
        $photos = $this->cache->remember(
            SiteCache::GALLERY,
            SiteCache::TEN_MINUTES,
            function (): array {
                $image = asset('assets/village-rice-fields.jpg');

                if (Schema::hasTable('galleries')) {
                    $photos = Gallery::where('status', 'published')->with(['items.media', 'cover'])->latest('event_date')->get()->flatMap(function ($gallery) use ($image) {
                        if ($gallery->items->isEmpty()) {
                            return [['src' => $gallery->cover?->url ?? $image, 'title' => $gallery->title, 'caption' => $gallery->description]];
                        }

                        return $gallery->items->map(fn ($item) => ['src' => $item->media->url, 'title' => $gallery->title, 'caption' => $item->caption ?? $gallery->description]);
                    })->all();
                    if ($photos !== []) {
                        return $photos;
                    }
                }

                return [
                    ['src' => $image, 'title' => 'Musyawarah Desa', 'caption' => 'Warga bermusyawarah untuk menyusun program desa.'],
                    ['src' => $image, 'title' => 'Kerja Bakti Warga', 'caption' => 'Gotong royong menjaga lingkungan tetap bersih.'],
                    ['src' => $image, 'title' => 'Pelatihan UMKM', 'caption' => 'Peningkatan kapasitas pelaku usaha lokal.'],
                    ['src' => $image, 'title' => 'Kegiatan Posyandu', 'caption' => 'Pelayanan kesehatan rutin untuk ibu dan anak.'],
                    ['src' => $image, 'title' => 'Panen Bersama', 'caption' => 'Dokumentasi potensi pertanian Desa Sukomulyo.'],
                    ['src' => $image, 'title' => 'Pentas Seni Desa', 'caption' => 'Ruang ekspresi seni dan budaya masyarakat.'],
                ];
            }
        );

        return $this->render('pages.gallery', compact('photos'));
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
        return $this->cache->remember(
            SiteCache::PUBLIC_LAYOUT,
            SiteCache::TEN_MINUTES,
            function (): array {
                $settings = $this->publicSettings();
                $setting = fn (string $key, string $fallback): string => (string) ($settings->get($key) ?: $fallback);

                return [
                    'site' => [
                        'name' => $setting('site.name', 'Desa Sukomulyo'),
                        'tagline' => $setting('site.tagline', 'Website Resmi Pemerintah Desa Sukomulyo'),
                        'email' => $setting('site.email', 'pemdes@sukomulyo.desa.id'),
                        'phone' => $setting('site.phone', '(0000) 123 456'),
                        'address' => $setting('site.address', 'Kantor Desa Sukomulyo, Indonesia'),
                    ],
                    'navigation' => $this->navigation(),
                    'articles' => $this->latestArticles(),
                    'categories' => $this->newsCategories(),
                    'archiveYears' => $this->newsArchiveYears(),
                ];
            }
        );
    }

    private function publicSettings(): Collection
    {
        return $this->cache->remember(
            SiteCache::SETTINGS,
            SiteCache::ONE_HOUR,
            fn () => Schema::hasTable('settings')
                ? Setting::query()->where('is_public', true)->pluck('value', 'key')
                : collect()
        );
    }

    private function navigation(): array
    {
        return $this->cache->remember(
            SiteCache::NAVIGATION,
            SiteCache::ONE_HOUR,
            fn () => [
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
            ]
        );
    }

    private function villageIdentity(): array
    {
        $settings = $this->publicSettings();
        $value = fn (string $key, string $fallback = ''): string => (string) ($settings->get($key) ?: $fallback);
        $villageHead = Schema::hasTable('officials')
            ? Official::where('position', 'Kepala Desa')->where('is_active', true)->orderBy('display_order')->first()
            : null;

        return [
            [
                'title' => 'Desa',
                'rows' => [
                    ['label' => 'Nama Desa', 'value' => $value('site.name', 'Desa Sukomulyo')],
                    ['label' => 'Kode Desa', 'value' => $value('village.code')],
                    ['label' => 'Kode BPS Desa', 'value' => $value('village.bps_code')],
                    ['label' => 'Kode Pos Desa', 'value' => $value('village.postal_code')],
                    ['label' => 'Nama Kepala Desa', 'value' => $villageHead?->full_name ?? ''],
                    ['label' => 'NIP Kepala Desa', 'value' => $villageHead?->nip ?? ''],
                    ['label' => 'Alamat Kantor Desa', 'value' => $value('site.address', 'Kantor Desa Sukomulyo, Indonesia')],
                    ['label' => 'E-Mail Desa', 'value' => $value('site.email', 'pemdes@sukomulyo.desa.id'), 'type' => 'email'],
                    ['label' => 'Nomor Telepon Desa', 'value' => $value('site.phone')],
                    ['label' => 'Nomor Ponsel Desa', 'value' => $value('village.mobile')],
                    ['label' => 'Website Desa', 'value' => $value('site.url'), 'type' => 'url'],
                ],
            ],
            [
                'title' => 'Kecamatan',
                'rows' => [
                    ['label' => 'Nama Kecamatan', 'value' => $value('district.name')],
                    ['label' => 'Kode Kecamatan', 'value' => $value('district.code')],
                ],
            ],
            [
                'title' => 'Kabupaten',
                'rows' => [
                    ['label' => 'Nama Kabupaten', 'value' => $value('regency.name')],
                    ['label' => 'Kode Kabupaten', 'value' => $value('regency.code')],
                ],
            ],
            [
                'title' => 'Provinsi',
                'rows' => [
                    ['label' => 'Nama Provinsi', 'value' => $value('province.name')],
                    ['label' => 'Kode Provinsi', 'value' => $value('province.code')],
                ],
            ],
        ];
    }

    private function articles(bool $includeUnpublished = false): array
    {
        if ($includeUnpublished) {
            return $this->loadArticles(true);
        }

        $articles = $this->cache->remember(
            SiteCache::NEWS_LIST,
            SiteCache::TEN_MINUTES,
            fn () => $this->loadArticles()
        );

        request()->attributes->set('site.published_articles', $articles);

        return $articles;
    }

    private function latestArticles(): array
    {
        return $this->cache->remember(
            SiteCache::LATEST_NEWS,
            SiteCache::TEN_MINUTES,
            function (): array {
                $articles = request()->attributes->get('site.published_articles');
                if (! is_array($articles)) {
                    $articles = Cache::get(SiteCache::NEWS_LIST);
                }

                return is_array($articles)
                    ? array_slice($articles, 0, 10)
                    : $this->loadArticles(limit: 10);
            }
        );
    }

    private function articleData(string $slug, bool $includeUnpublished = false): ?array
    {
        if ($includeUnpublished) {
            return collect($this->loadArticles(true))->firstWhere('slug', $slug);
        }

        $article = $this->cache->remember(
            $this->cache->newsDetailKey($slug),
            SiteCache::TEN_MINUTES,
            function () use ($slug): array|false {
                if (! Schema::hasTable('news')) {
                    return collect($this->fallbackArticles())->firstWhere('slug', $slug) ?: false;
                }

                $article = News::with(['category', 'featuredImage'])
                    ->published()
                    ->where('slug', $slug)
                    ->first();

                if ($article) {
                    return $this->mapArticle($article);
                }

                return News::published()->exists()
                    ? false
                    : (collect($this->fallbackArticles())->firstWhere('slug', $slug) ?: false);
            }
        );

        return is_array($article) ? $article : null;
    }

    private function newsCategories(): array
    {
        return $this->cache->remember(
            SiteCache::NEWS_CATEGORIES,
            SiteCache::TEN_MINUTES,
            function (): array {
                $articles = request()->attributes->get('site.published_articles');
                if (! is_array($articles)) {
                    $articles = Cache::get(SiteCache::NEWS_LIST);
                }
                if (is_array($articles)) {
                    return collect($articles)->unique('category_slug')->values()->all();
                }

                if (Schema::hasTable('news') && Schema::hasTable('news_categories')) {
                    $categories = NewsCategory::query()
                        ->whereHas('news', fn ($query) => $query->published())
                        ->orderBy('name')
                        ->get(['name', 'slug'])
                        ->map(fn ($category) => [
                            'category' => $category->name,
                            'category_slug' => $category->slug,
                        ])
                        ->all();

                    if ($categories !== []) {
                        return $categories;
                    }
                }

                return collect($this->fallbackArticles())->unique('category_slug')->values()->all();
            }
        );
    }

    private function newsArchiveYears(): array
    {
        return $this->cache->remember(
            SiteCache::NEWS_ARCHIVES,
            SiteCache::TEN_MINUTES,
            function (): array {
                $articles = request()->attributes->get('site.published_articles');
                if (! is_array($articles)) {
                    $articles = Cache::get(SiteCache::NEWS_LIST);
                }
                if (is_array($articles)) {
                    return collect($articles)->pluck('year')->unique()->values()->all();
                }

                if (Schema::hasTable('news')) {
                    $years = News::published()
                        ->latest('published_at')
                        ->get(['published_at', 'created_at'])
                        ->map(fn (News $article) => (string) ($article->published_at ?? $article->created_at)->year)
                        ->unique()
                        ->values()
                        ->all();

                    if ($years !== []) {
                        return $years;
                    }
                }

                return collect($this->fallbackArticles())->pluck('year')->unique()->values()->all();
            }
        );
    }

    private function loadArticles(bool $includeUnpublished = false, ?int $limit = null): array
    {
        if (! Schema::hasTable('news')) {
            return $limit === null
                ? $this->fallbackArticles()
                : array_slice($this->fallbackArticles(), 0, $limit);
        }

        $query = News::with(['category', 'featuredImage']);
        if (! $includeUnpublished) {
            $query->published();
        }
        if ($limit !== null) {
            $query->limit($limit);
        }

        $articles = $query->latest('published_at')->get()->map(
            fn (News $article) => $this->mapArticle($article)
        )->all();

        if ($articles !== []) {
            return $articles;
        }

        return $limit === null
            ? $this->fallbackArticles()
            : array_slice($this->fallbackArticles(), 0, $limit);
    }

    private function mapArticle(News $article): array
    {
        $image = asset('assets/village-rice-fields.jpg');
        $htmlContent = preg_replace('~https?://(?:localhost|127\.0\.0\.1)(?::\d+)?(/storage/)~i', '$1', $article->content);
        preg_match('~<img[^>]+src=["\']([^"\']+)["\']~i', $htmlContent, $inlineImage);

        return [
            'slug' => $article->slug,
            'title' => $article->title,
            'date' => ($article->published_at ?? $article->created_at)->translatedFormat('d F Y'),
            'year' => (string) ($article->published_at ?? $article->created_at)->year,
            'category' => $article->category->name,
            'category_slug' => $article->category->slug,
            'image' => $article->featuredImage?->url ?? ($inlineImage[1] ?? $image),
            'featured_image' => $article->featuredImage ? [
                'url' => $article->featuredImage->url,
                'alt' => $article->featuredImage->alt_text ?: $article->title,
            ] : null,
            'excerpt' => $article->excerpt ?? strip_tags($htmlContent),
            'content' => [strip_tags($htmlContent)],
            'html_content' => $htmlContent,
            'tags' => [],
        ];
    }

    private function fallbackArticles(): array
    {
        $image = asset('assets/village-rice-fields.jpg');

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

    private function galleryPhotos(): array
    {
        $image = asset('assets/village-rice-fields.jpg');

        return [
            ['src' => $image, 'title' => 'Musyawarah Desa', 'caption' => 'Warga bermusyawarah untuk menyusun program desa.'],
            ['src' => $image, 'title' => 'Kerja Bakti Warga', 'caption' => 'Gotong royong menjaga lingkungan tetap bersih.'],
            ['src' => $image, 'title' => 'Pelatihan UMKM', 'caption' => 'Peningkatan kapasitas pelaku usaha lokal.'],
            ['src' => $image, 'title' => 'Kegiatan Posyandu', 'caption' => 'Pelayanan kesehatan rutin untuk ibu dan anak.'],
            ['src' => $image, 'title' => 'Panen Bersama', 'caption' => 'Dokumentasi potensi pertanian Desa Sukomulyo.'],
            ['src' => $image, 'title' => 'Pentas Seni Desa', 'caption' => 'Ruang ekspresi seni dan budaya masyarakat.'],
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
