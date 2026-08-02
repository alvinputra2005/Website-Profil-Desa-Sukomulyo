<?php

namespace App\Services\Web;

use App\Http\Requests\Web\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Models\Gallery;
use App\Models\IdmScore;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Official;
use App\Models\Publication;
use App\Models\Setting;
use App\Models\StatisticCategory;
use App\Models\StatisticDataset;
use App\Models\VillageComment;
use App\Models\VillageProfileSection;
use App\Services\BudgetHistoryData;
use App\Services\PopulationStatistics as PopulationStatisticsService;
use App\Services\SiteCache;
use App\Services\Statistics\PopulationStatisticIndicatorService;
use App\Services\Statistics\PopulationStatisticAggregator;
use App\Models\PopulationStatisticIndicator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PublicSiteService
{
    public function __construct(
        private readonly SiteCache $cache,
        private readonly AdministrativeServicePage $administrativeServicePage,
        private readonly PopulationStatisticIndicatorService $populationIndicators,
        private readonly PopulationStatisticAggregator $populationAggregator,
        private readonly BudgetHistoryData $budgetHistoryData,
    ) {}

    public function sitemap(): Response
    {
        $urls = $this->cache->remember(SiteCache::SEO_SITEMAP, SiteCache::ONE_HOUR, function (): array {
            $staticRoutes = [
                'beranda', 'profile-desa', 'pemerintahan-desa', 'potensi-desa',
                'data-desa-statistik', 'informasi-publik-desa',
                'announcements.index', 'galeri-desa', 'berita-desa.index', 'kontak.index',
            ];
            $urls = collect($staticRoutes)->map(fn (string $route) => [
                'loc' => route($route),
                'lastmod' => now()->toDateString(),
            ]);

            if (Schema::hasTable('news')) {
                $urls = $urls->concat(
                    News::published()->get(['slug', 'updated_at'])->map(fn (News $news) => [
                        'loc' => route('berita-desa.show', $news->slug),
                        'lastmod' => $news->updated_at->toDateString(),
                    ])
                );
            }

            if (Schema::hasTable('publications')) {
                $urls = $urls->concat(
                    Publication::query()->announcements()->published()->get(['slug', 'updated_at'])->map(fn (Publication $publication) => [
                        'loc' => route('announcements.show', ['publication' => $publication->slug]),
                        'lastmod' => $publication->updated_at->toDateString(),
                    ])
                );
            }

            return $urls->values()->all();
        });

        return response()
            ->view('seo.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $body = implode("\n", [
            'User-agent: *',
            'Disallow: /admin/',
            'Disallow: /preview/',
            'Disallow: /storage/private/',
            'Sitemap: '.route('sitemap'),
            '',
        ]);

        return response($body)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function home(PopulationStatisticsService $populationStatistics): View
    {
        $apbdes = $this->homeApbdes($this->budgetHistoryData->latest());
        $populationSummary = $this->cache->remember(
            SiteCache::HOME_STATISTICS,
            SiteCache::TEN_MINUTES,
            fn () => Schema::hasTable('residents')
                ? $populationStatistics->summary()
                : ['residents' => 0, 'male' => 0, 'female' => 0, 'families' => 0, 'areas' => 0, 'education_records' => 0, 'occupation_records' => 0]
        );
        $censusGender = $populationStatistics->censusGenderSummary();
        if ($censusGender) {
            $populationSummary['male'] = $censusGender['male'];
            $populationSummary['female'] = $censusGender['female'];
            $populationSummary['residents'] = $censusGender['total'];
        }

        return $this->render('pages.home', [
            'villageStatistics' => [
                ['icon' => 'fas fa-users', 'value' => $populationSummary['residents'], 'unit' => 'jiwa', 'label' => 'Jumlah Penduduk'],
                ['icon' => 'fas fa-home', 'value' => $populationSummary['families'], 'unit' => 'KK', 'label' => 'Kepala Keluarga'],
                ['icon' => 'fas fa-graduation-cap', 'value' => $populationSummary['education_records'], 'unit' => 'jiwa', 'label' => 'Data Pendidikan'],
                ['icon' => 'fas fa-briefcase', 'value' => $populationSummary['occupation_records'], 'unit' => 'jiwa', 'label' => 'Data Pekerjaan'],
            ],
            'populationByGender' => [
                ['label' => 'Laki-laki', 'value' => $populationSummary['male'], 'image' => 'assets/male-resident-avatar.jpg'],
                ['label' => 'Perempuan', 'value' => $populationSummary['female'], 'image' => 'assets/female-resident-avatar.jpg'],
            ],
            'apbdes' => $apbdes,
            'galleryPhotos' => array_slice($this->galleryPhotos(), 0, 5),
        ]);
    }

    private function homeApbdes(array $budget): array
    {
        $year = (int) $budget['summary']['year'];
        $income = (float) $budget['summary']['income'];
        $spending = (float) $budget['summary']['spending'];

        $composition = static fn (array $item, float $total): array => [
            'label' => $item['short_name'] ?? $item['name'],
            'value' => (float) ($item['budget'] ?? 0),
            'percentage' => $total > 0
                ? round((float) ($item['budget'] ?? 0) / $total * 100, 2)
                : 0,
        ];

        return [
            'year' => $year,
            'panels' => [
                [
                    'title' => "Pendapatan APBDes {$year}",
                    'total' => $income,
                    'total_label' => "Total Pendapatan APBDes {$year}",
                    'total_percentage' => 100,
                    'items' => collect($budget['revenue'] ?? [])
                        ->map(fn (array $item): array => $composition($item, $income))
                        ->values()
                        ->all(),
                ],
                [
                    'title' => "Belanja APBDes {$year}",
                    'total' => $spending,
                    'total_label' => "Total Penggunaan Belanja APBDes {$year}",
                    'total_percentage' => 100,
                    'items' => collect($budget['spending'] ?? [])
                        ->map(fn (array $item): array => $composition($item, $spending))
                        ->values()
                        ->all(),
                ],
                [
                    'title' => "Realisasi APBDes {$year}",
                    'total' => (float) $budget['summary']['realization'],
                    'total_label' => "Total Realisasi APBDes {$year}",
                    'total_percentage' => (float) $budget['summary']['percentage'],
                    'items' => collect($budget['spending'] ?? [])
                        ->map(fn (array $item): array => [
                            'label' => $item['short_name'] ?? $item['name'],
                            'value' => (float) ($item['realization'] ?? 0),
                            'percentage' => (float) ($item['percentage'] ?? 0),
                        ])
                        ->values()
                        ->all(),
                ],
            ],
        ];
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
                'villageLeader' => $this->villageLeader(),
                'villageRegulations' => $this->villageRegulations(),
            ]
        );

        // Older cached profile payloads may not yet contain the sidebar data.
        $profile['villageLeader'] ??= $this->villageLeader();
        $profile['villageRegulations'] ??= $this->villageRegulations();
        $profile = array_merge($profile, $this->profilePageData('identitas'));

        return $this->render('pages.profile', $profile);
    }

    public function sendProfileComment(Request $request): RedirectResponse
    {
        $request->merge([
            'page_key' => $request->input('page_key', 'identitas'),
        ]);

        $comment = $request->validate([
            'page_key' => ['required', Rule::in(array_keys($this->profilePages()))],
            'comment' => ['required', 'string', 'max:1500'],
            'name' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:300'],
            'phone' => ['required', 'string', 'max:25', 'regex:/^[0-9+().\s-]{8,25}$/'],
            'website' => ['nullable', 'max:0'],
        ], [
            'comment.required' => 'Isi komentar wajib dituliskan.',
            'name.required' => 'Nama wajib diisi.',
            'address.required' => 'Alamat wajib diisi.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.regex' => 'Format nomor HP belum sesuai.',
        ]);

        unset($comment['website']);
        $comment['status'] = 'pending';
        $comment['is_visible'] = false;
        VillageComment::create($comment);

        $page = $this->profilePages()[$comment['page_key']];

        return redirect()
            ->to($page['url'].'#komentar')
            ->with('comment_success', 'Terima kasih. Komentar Anda sudah diterima dan akan ditinjau admin.');
    }

    public function profileComments(): View
    {
        return $this->renderProfileComments('identitas');
    }

    public function profileSectionComments(string $section): View
    {
        abort_unless(isset($this->profilePages()[$section]), 404);

        return $this->renderProfileComments($section);
    }

    private function renderProfileComments(string $pageKey): View
    {
        $page = $this->profilePages()[$pageKey];
        $comments = Schema::hasTable('village_comments')
            ? VillageComment::query()
                ->where('page_key', $pageKey)
                ->where('status', 'approved')
                ->latest()
                ->paginate(12)
            : new LengthAwarePaginator([], 0, 12);

        return $this->render('pages.profile-comments', compact('comments', 'page'));
    }

    public function likeProfileComment(VillageComment $comment): RedirectResponse
    {
        abort_unless($comment->status === 'approved' && $comment->is_visible, 404);

        $comment->increment('like_count');

        return redirect()->back();
    }

    public function profileDetail(string $section): View
    {
        $pages = [
            'sejarah' => [
                'title' => 'Sejarah Desa',
                'description' => 'Asal-usul dan perkembangan Desa Sukomulyo dari masa ke masa.',
                'keys' => ['history'],
                'fallback' => [['title' => 'Sejarah Desa Sukomulyo', 'content' => 'Desa Sukomulyo tumbuh melalui semangat gotong royong, kebersamaan, dan kerja keras masyarakat.']],
            ],
            'visi-misi' => [
                'title' => 'Visi dan Misi',
                'description' => 'Arah pembangunan dan cita-cita Pemerintah Desa Sukomulyo.',
                'keys' => ['vision', 'mission'],
                'fallback' => [
                    ['title' => 'Visi Desa', 'content' => 'Terwujudnya desa yang maju, mandiri, transparan, dan sejahtera.'],
                    ['title' => 'Misi Desa', 'content' => 'Meningkatkan pelayanan publik, ekonomi warga, dan pembangunan berkelanjutan.'],
                ],
            ],
        ];
        abort_unless(isset($pages[$section]), 404);
        $page = $pages[$section];
        $sections = Schema::hasTable('village_profile_sections')
            ? VillageProfileSection::with('image')->whereIn('section_key', $page['keys'])->where('status', 'published')->orderBy('display_order')->get()
            : collect();

        return $this->render('pages.profile-detail', array_merge(
            compact('page', 'sections'),
            $this->profilePageData($section)
        ));
    }

    public function statistics(PopulationStatisticsService $populationStatistics): View
    {
        $statistics = $this->cache->remember(
            SiteCache::PUBLIC_STATISTICS,
            SiteCache::TEN_MINUTES,
            function () use ($populationStatistics): array {
                $summary = Schema::hasTable('residents')
                    ? $populationStatistics->summary()
                    : ['residents' => 0, 'male' => 0, 'female' => 0, 'families' => 0, 'areas' => 0];
                $censusGender = $populationStatistics->censusGenderSummary();
                if ($censusGender) {
                    $summary['male'] = $censusGender['male'];
                    $summary['female'] = $censusGender['female'];
                    $summary['residents'] = $censusGender['total'];
                }
                $total = max($summary['residents'], 1);
                $populationIndicators = $this->populationIndicators->availableIndicators(true);
                $importedCategories = $this->publishedStatisticCategories();
                $indicatorResults = $populationIndicators
                    ->map(function (PopulationStatisticIndicator $indicator) use ($censusGender): array {
                        if ($censusGender && $indicator->key === 'gender') {
                            $items = [
                                ['key' => 'L', 'label' => 'Laki-laki', 'value' => $censusGender['male'], 'total' => $censusGender['male'], 'percentage' => $censusGender['male_percentage']],
                                ['key' => 'P', 'label' => 'Perempuan', 'value' => $censusGender['female'], 'total' => $censusGender['female'], 'percentage' => $censusGender['female_percentage']],
                            ];
                        } else {
                            $items = collect($this->populationAggregator->aggregate($indicator)['items'])
                                ->map(fn (array $item): array => $item + ['total' => $item['value']])
                                ->all();
                        }

                        return ['title' => $indicator->label, 'items' => $items];
                    });
                $baseCards = collect([
                    [
                        'key' => 'kependudukan',
                        'label' => 'Kependudukan',
                        'icon' => 'fas fa-users',
                        'description' => 'Pilih indikator penduduk yang ingin dibuka.',
                        'options' => $populationIndicators->map(fn (PopulationStatisticIndicator $indicator): array => [
                            'label' => $indicator->label,
                            'url' => route('data-statistik.population', ['indicator' => $indicator->key]),
                        ])->all(),
                    ],
                    [
                        'key' => 'keluarga',
                        'label' => 'Keluarga',
                        'icon' => 'fas fa-home',
                        'description' => 'Data keluarga, kepala keluarga, dan anggota keluarga.',
                        'options' => [
                            ['label' => 'Jumlah Keluarga', 'url' => route('data-statistik.detail', ['section' => 'keluarga'])],
                            ['label' => 'Kepala Keluarga', 'url' => route('data-statistik.detail', ['section' => 'keluarga', 'menu' => 'kepala-keluarga'])],
                            ['label' => 'Anggota Keluarga', 'url' => route('data-statistik.detail', ['section' => 'keluarga', 'menu' => 'anggota-keluarga'])],
                        ],
                    ],
                ])->keyBy('key');

                foreach ($importedCategories as $category) {
                    $importedOptions = collect([
                        ['label' => 'Lihat semua '.$category->name, 'url' => route('data-statistik.detail', ['section' => $category->slug])],
                    ])->concat($category->datasets->map(fn (StatisticDataset $dataset): array => [
                        'label' => trim(($dataset->short_title ?: $dataset->title).' · '.($dataset->period ?: $dataset->year)),
                        'url' => route('data-statistik.imported.show', [
                            'category' => $category->slug,
                            'dataset' => $dataset->slug,
                        ]),
                    ]))->all();

                    if ($baseCards->has($category->slug)) {
                        $card = $baseCards->get($category->slug);
                        $card['options'] = collect($card['options'])->concat($importedOptions)->values()->all();
                        $card['description'] = $card['description'].' '.$category->datasets->count().' dataset terpublikasi.';
                        $baseCards->put($category->slug, $card);

                        continue;
                    }

                    $baseCards->put($category->slug, [
                        'key' => $category->slug,
                        'label' => $category->name,
                        'icon' => 'fas '.($category->icon ?: 'fa-table'),
                        'description' => $category->datasets->count().' dataset terpublikasi.',
                        'options' => $importedOptions,
                    ]);
                }

                $statisticCards = $baseCards->values();

                return [
                    'statistics' => [
                        ['icon' => 'fas fa-users', 'value' => number_format($summary['residents'], 0, ',', '.'), 'label' => 'Jumlah Penduduk', 'unit' => 'jiwa'],
                        ['icon' => 'fas fa-home', 'value' => number_format($summary['families'], 0, ',', '.'), 'label' => 'Kepala Keluarga', 'unit' => 'KK'],
                        ['icon' => 'fas fa-map-signs', 'value' => number_format($summary['areas'], 0, ',', '.'), 'label' => 'Wilayah Dusun', 'unit' => 'dusun'],
                    ],
                    'population' => [
                        ['label' => 'Laki-laki', 'value' => $summary['male'], 'percentage' => round($summary['male'] / $total * 100, 2)],
                        ['label' => 'Perempuan', 'value' => $summary['female'], 'percentage' => round($summary['female'] / $total * 100, 2)],
                    ],
                    'livelihoods' => [],
                    'distributions' => $indicatorResults
                        ->mapWithKeys(fn (array $panel, int $index): array => ['indikator-'.$index => $panel])
                        ->all(),
                    'statisticCards' => $statisticCards->all(),
                    'idm' => Schema::hasTable('idm_scores')
                        ? IdmScore::query()->latest('year')->first()?->only(['year', 'idm_score', 'iks_score', 'ike_score', 'ikl_score', 'status_label', 'source'])
                        : null,
                ];
            }
        );

        return $this->render('pages.statistics', $statistics + [
            'importedStatisticCategories' => $this->publishedStatisticCategories(),
        ]);
    }

    public function statisticDetail(string $section, PopulationStatisticsService $populationStatistics): View
    {
        $dataPages = [
            'penduduk' => ['title' => 'Statistik Penduduk', 'description' => 'Jumlah penduduk, jenis kelamin, usia, dan kepala keluarga.', 'categories' => ['sex'], 'summary' => true],
            'keluarga' => ['title' => 'Statistik Keluarga', 'description' => 'Ringkasan jumlah keluarga, penduduk, dan wilayah desa.', 'categories' => [], 'summary' => true, 'summary_cards' => [
                ['Jumlah Keluarga', 'families', 'KK', 'fas fa-home'],
                ['Jumlah Penduduk', 'residents', 'jiwa', 'fas fa-users'],
                ['Wilayah Dusun', 'areas', 'dusun', 'fas fa-map-signs'],
            ]],
            'pendidikan' => ['title' => 'Statistik Pendidikan', 'description' => 'Jumlah penduduk berdasarkan jenjang pendidikan.', 'categories' => ['education']],
            'pekerjaan' => ['title' => 'Statistik Pekerjaan', 'description' => 'Sebaran pekerjaan dan mata pencaharian masyarakat.', 'categories' => ['occupation']],
            'ekonomi' => ['title' => 'Statistik Ekonomi', 'description' => 'Gambaran aktivitas dan potensi ekonomi masyarakat desa.', 'categories' => ['occupation']],
            'idm' => ['title' => 'IDM (Indeks Desa Membangun)', 'description' => 'Indeks Ketahanan Sosial, Ekonomi, dan Lingkungan desa.', 'categories' => [], 'is_idm' => true],
            'visualisasi' => ['title' => 'Visualisasi Data', 'description' => 'Ringkasan data desa dalam tabel, grafik, dan angka.', 'categories' => ['age', 'education', 'occupation'], 'summary' => true],
        ];
        abort_unless(isset($dataPages[$section]), 404);
        $page = $dataPages[$section];
        $hasResidents = Schema::hasTable('residents');
        $summary = $hasResidents
            ? $populationStatistics->summary()
            : ['residents' => 0, 'male' => 0, 'female' => 0, 'families' => 0, 'areas' => 0];
        $categoryLabels = [
            'sex' => 'Jenis Kelamin',
            'age' => 'Kelompok Umur',
            'education' => 'Pendidikan',
            'occupation' => 'Pekerjaan',
            'religion' => 'Agama',
            'marital_status' => 'Status Perkawinan',
        ];
        $panels = collect($page['categories'])->map(fn (string $category) => [
            'title' => $categoryLabels[$category],
            'items' => $hasResidents ? $populationStatistics->distribution($category) : [],
        ])->all();
        $idm = $section === 'idm' && Schema::hasTable('idm_scores')
            ? IdmScore::query()->latest('year')->first()?->only(['year', 'idm_score', 'iks_score', 'ike_score', 'ikl_score', 'status_label', 'source'])
            : null;

        return $this->render('pages.statistic-detail', compact('page', 'summary', 'panels', 'idm'));
    }

    public function populationStatistics(
        Collection $indicators,
        ?PopulationStatisticIndicator $selectedIndicator,
        ?array $result,
    ): View
    {
        $year = (int) config('village.population_year', now()->year);
        $censusGender = app(PopulationStatisticsService::class)->censusGenderSummary();
        $items = collect($result['items'] ?? []);
        $genderSummary = [
            'year' => $censusGender && $selectedIndicator?->key === 'gender' ? $censusGender['year'] : $year,
            'male' => (int) data_get($items->firstWhere('key', 'L'), 'value', 0),
            'female' => (int) data_get($items->firstWhere('key', 'P'), 'value', 0),
            'total' => (int) ($result['total'] ?? 0),
            'male_percentage' => (float) data_get($items->firstWhere('key', 'L'), 'percentage', 0),
            'female_percentage' => (float) data_get($items->firstWhere('key', 'P'), 'percentage', 0),
            'updated_at' => null,
            'source' => $censusGender && $selectedIndicator?->key === 'gender' ? $censusGender['source'] : 'Administrasi Kependudukan Desa',
        ];

        if ($censusGender && $selectedIndicator?->key === 'gender') {
            $genderSummary = array_merge($genderSummary, $censusGender);
        }

        return $this->render('pages.population-statistics', [
            'page' => [
                'title' => 'Statistik Penduduk',
                'description' => 'Komposisi penduduk Desa Sukomulyo berdasarkan data administrasi kependudukan saat ini.',
            ],
            'genderSummary' => $genderSummary,
            'populationIndicators' => $indicators,
            'selectedIndicator' => $selectedIndicator,
            'result' => $result,
        ]);
    }

    public function genericStatistic(array $data): View
    {
        return $this->render('pages.generic-statistics', $data);
    }

    public function importedStatisticCategory(StatisticCategory $category): View
    {
        $categories = $this->publishedStatisticCategories();
        $category = $categories->firstWhere('id', $category->id);
        abort_unless($category, 404);

        return $this->render('pages.imported-statistics-category', [
            'category' => $category,
            'statisticCategories' => $categories,
        ]);
    }

    public function importedStatisticDataset(
        StatisticCategory $category,
        StatisticDataset $dataset,
    ): View {
        $categories = $this->publishedStatisticCategories();
        $category = $categories->firstWhere('id', $category->id);
        abort_unless($category && $dataset->statistic_category_id === $category->id, 404);

        $dataset->load('rows');

        return $this->render('pages.imported-statistics-show', [
            'category' => $category,
            'dataset' => $dataset,
            'statisticCategories' => $categories,
        ]);
    }

    public function findPublishedStatisticCategory(string $slug): ?StatisticCategory
    {
        return $this->publishedStatisticCategories()->firstWhere('slug', $slug);
    }

    public function budgetHistory(array $budgetHistory): View
    {
        return $this->render('pages.budget-history', compact('budgetHistory'));
    }

    public function budgetDetail(array $budget): View
    {
        return $this->render('pages.budget-detail', compact('budget'));
    }

    public function publicInformation(): View
    {
        $latestBudgetYear = (string) data_get($this->budgetHistoryData->latest(), 'summary.year', BudgetHistoryData::LATEST_PUBLIC_YEAR);
        $documents = $this->publicInformationDocuments();

        return $this->render('pages.public-information', [
            'documents' => $documents ?: [
                ['icon' => 'fas fa-file-pdf', 'title' => 'APBDes Desa Sukomulyo', 'category' => 'Keuangan Desa', 'year' => $latestBudgetYear],
                ['icon' => 'fas fa-file-alt', 'title' => 'Rencana Kerja Pemerintah Desa', 'category' => 'Perencanaan', 'year' => '2026'],
                ['icon' => 'fas fa-clipboard-list', 'title' => 'Laporan Penyelenggaraan Pemerintahan Desa', 'category' => 'Laporan', 'year' => '2025'],
                ['icon' => 'fas fa-bullhorn', 'title' => 'Standar Pelayanan Publik Desa', 'category' => 'Pelayanan', 'year' => '2026'],
            ],
        ]);
    }

    private function publicInformationDocuments(): array
    {
        if (! Schema::hasTable('publications') || ! Schema::hasTable('publication_attachments')) {
            return [];
        }

        return Publication::query()
            ->published()
            ->with(['attachments.media'])
            ->where('type', 'document')
            ->latest('published_at')
            ->get()
            ->map(function (Publication $publication): array {
                $attachment = $publication->attachments
                    ->first(fn ($item) => $item->media && $item->media->mime_type === 'application/pdf');

                $year = (string) ($publication->start_date?->year ?? $publication->published_at?->year ?? now()->year);
                return [
                    'icon' => 'fas fa-file-pdf',
                    'title' => $publication->title,
                    'category' => 'Dokumen Publik',
                    'year' => $year,
                    'preview_url' => $attachment ? route('publications.attachments.preview', [
                        'publication' => $publication->slug,
                        'attachment' => $attachment->id,
                    ]) : null,
                    'download_url' => $attachment ? route('publications.attachments.download', [
                        'publication' => $publication->slug,
                        'attachment' => $attachment->id,
                    ]) : null,
                ];
            })
            ->values()
            ->all();
    }

    public function informationDetail(string $section): View
    {
        if ($section === 'layanan-administrasi') {
            return $this->render(
                'pages.administrative-services',
                $this->administrativeServicePage->data(),
            );
        }

        $pages = [
            'agenda' => [
                'title' => 'Agenda Desa',
                'description' => 'Jadwal kegiatan desa, musyawarah, dan kegiatan masyarakat.',
                'type' => 'agenda',
                'fallback' => [['title' => 'Belum Ada Agenda Terbaru', 'content' => 'Jadwal kegiatan desa akan ditampilkan pada halaman ini.']],
            ],
            'bantuan-sosial' => [
                'title' => 'Informasi Bantuan Sosial',
                'description' => 'Jadwal bantuan, syarat penerima, dan informasi penyaluran.',
                'fallback' => [
                    ['title' => 'Jadwal Penyaluran', 'content' => 'Jadwal penyaluran bantuan diumumkan setelah pemerintah desa menerima informasi resmi.'],
                    ['title' => 'Syarat Penerima', 'content' => 'Penerima mengikuti hasil pendataan dan ketentuan program bantuan yang berlaku.'],
                    ['title' => 'Informasi Penyaluran', 'content' => 'Konfirmasi penyaluran dapat diperoleh melalui kantor desa atau pengumuman resmi desa.'],
                ],
            ],
            'informasi-publik' => [
                'title' => 'Informasi Publik',
                'description' => 'Program kerja dan dokumen publik.',
                'type' => 'document',
                'fallback' => [
                    ['title' => 'Program Kerja Desa', 'content' => 'Informasi program kerja pemerintah desa tersedia untuk mendukung keterbukaan publik.'],
                ],
            ],
        ];
        abort_unless(isset($pages[$section]), 404);
        $page = $pages[$section];
        $items = collect();
        if (isset($page['type']) && Schema::hasTable('publications')) {
            $items = Publication::published()
                ->when(
                    is_array($page['type']),
                    fn ($query) => $query->whereIn('type', $page['type']),
                    fn ($query) => $query->where('type', $page['type']),
                )
                ->latest('published_at')
                ->get()
                ->map(fn (Publication $publication) => [
                'title' => $publication->title,
                'content' => $publication->content,
                'excerpt' => $publication->excerpt,
                'html' => true,
                'date' => $publication->start_date?->translatedFormat('d F Y') ?? $publication->published_at?->translatedFormat('d F Y'),
            ]);
        }
        if ($items->isEmpty()) {
            $items = collect($page['fallback']);
        }

        return $this->render('pages.information-detail', compact('page', 'items'));
    }

    public function page(string $view, array $data = []): View
    {
        return $this->render($view, $data);
    }

    public function government(): View
    {
        return $this->render('pages.government', array_merge(
            $this->profilePageData('struktur-pemerintahan')
        ));
    }

    public function potentials(): View
    {
        return $this->render('pages.potentials', array_merge(
            ['potentials' => $this->potentialsData()],
            $this->profilePageData('potensi-desa')
        ));
    }

    public function news(Request $request): View
    {
        $selectedCategory = trim((string) $request->query('category', ''));
        $selectedYear = trim((string) $request->query('year', ''));
        $articles = collect($this->articles());

        if ($selectedCategory !== '') {
            $articles = $articles->where('category_slug', $selectedCategory);
        }

        if ($selectedYear !== '') {
            $articles = $articles->where('year', $selectedYear);
        }

        // Berita utama selalu berasal dari tahun berjalan. Berita tahun
        // sebelumnya tetap tersedia melalui daftar berita dan menu arsip.
        $featuredArticles = $articles
            ->filter(fn (array $article): bool => $article['year'] === (string) now()->year)
            ->take(3)
            ->values()
            ->all();

        return $this->renderNews($request, [
            'heading' => 'Berita Desa',
            'description' => 'Informasi terbaru mengenai kegiatan dan perkembangan Desa Sukomulyo.',
            'visibleArticles' => $this->paginateArticles($articles, $request),
            'featuredArticles' => $featuredArticles,
            'showFeatured' => $selectedCategory === '' && $selectedYear === '',
            'selectedCategory' => $selectedCategory,
            'selectedYear' => $selectedYear,
        ]);
    }

    public function article(string $slug): View|Response
    {
        $canPreviewDraft = auth()->check() && auth()->user()->can('manage-content');

        if (! $canPreviewDraft && Schema::hasTable('news')) {
            $viewed = News::query()
                ->published()
                ->where('slug', $slug)
                ->increment('view_count');

            if ($viewed > 0) {
                $this->cache->invalidateNews();
            }
        }

        $article = $this->articleData($slug, $canPreviewDraft);

        if (! $article) {
            return $this->notFound();
        }

        return $this->render('news.show', ['article' => $article]);
    }

    public function category(Request $request, string $category): View|Response
    {
        $categoryArticles = collect($this->articles())
            ->filter(fn (array $article) => $article['category_slug'] === $category)
            ->values();

        if ($categoryArticles->isEmpty()) {
            return $this->notFound();
        }

        $selectedYear = trim((string) $request->query('year', ''));
        $articles = $selectedYear === ''
            ? $categoryArticles
            : $categoryArticles->where('year', $selectedYear)->values();

        return $this->renderNews($request, [
            'heading' => 'Kategori: '.$categoryArticles->first()['category'],
            'description' => 'Kumpulan berita dalam kategori '.$categoryArticles->first()['category'].'.',
            'visibleArticles' => $this->paginateArticles($articles, $request),
            'featuredArticles' => [],
            'showFeatured' => false,
            'selectedCategory' => $category,
            'selectedYear' => $selectedYear,
        ]);
    }

    public function archive(Request $request, ?string $year = null): View
    {
        $year ??= collect($this->articles())->max('year');
        $selectedCategory = trim((string) $request->query('category', ''));
        $articles = collect($this->articles())
            ->where('year', $year)
            ->when($selectedCategory !== '', fn (Collection $articles) => $articles->where('category_slug', $selectedCategory))
            ->values();
        $showFeatured = (string) $year === (string) now()->year
            && $selectedCategory === '';

        return $this->renderNews($request, [
            'heading' => 'Arsip Berita '.$year,
            'description' => 'Dokumentasi berita dan kegiatan Desa Sukomulyo pada tahun '.$year.'.',
            'visibleArticles' => $this->paginateArticles($articles, $request),
            'featuredArticles' => $showFeatured ? $articles->take(3)->values()->all() : [],
            'showFeatured' => $showFeatured,
            'selectedCategory' => $selectedCategory,
            'selectedYear' => (string) $year,
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

        return $this->renderNews($request, compact('query', 'results'), 'search');
    }

    private function paginateArticles(Collection $articles, Request $request): LengthAwarePaginator
    {
        $perPage = 5;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $articles->forPage($currentPage, $perPage)->values()->all(),
            $articles->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    public function gallery(Request $request): View
    {
        $photos = $this->galleryAlbums();

        $photos = array_map(function (array $photo): array {
            $photo['src'] = $this->normalizeFallbackImage($photo['src'] ?? null);

            return $photo;
        }, $photos);

        $photoCollection = collect($photos);
        $perPage = 6;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $photos = new LengthAwarePaginator(
            $photoCollection->forPage($currentPage, $perPage)->values()->all(),
            $photoCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        if ($request->header('X-Ajax-Root') === '#gallery-ajax-root') {
            view()->share($this->shared());

            return view('pages.partials.gallery-content', compact('photos'));
        }

        return $this->render('pages.gallery', compact('photos'));
    }

    public function contact(): View
    {
        return $this->render('pages.contact');
    }

    public function sendContact(StoreContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::create($request->validated());

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

    public function shareLayout(): void
    {
        view()->share($this->shared());
    }

    private function renderNews(Request $request, array $data, string $page = 'index'): View
    {
        $view = $page === 'search' ? 'news.search' : 'news.index';
        $partial = $page === 'search' ? 'news.partials.search-content' : 'news.partials.index-content';

        if ($request->header('X-Ajax-Root') === '#news-ajax-root') {
            view()->share($this->shared());

            return view($partial, $data);
        }

        return $this->render($view, $data);
    }

    private function shared(): array
    {
        $layout = $this->cache->remember(
            SiteCache::PUBLIC_LAYOUT,
            SiteCache::TEN_MINUTES,
            function (): array {
                $settings = $this->publicSettings();
                $setting = fn (string $key, string $fallback): string => (string) ($settings->get($key) ?: $fallback);

                return [
                    'site' => [
                        'name' => $setting('site.name', 'Desa Sukomulyo'),
                        'tagline' => $setting('site.tagline', 'Website Resmi Pemerintah Desa Sukomulyo'),
                        'email' => $setting('site.email', 'desasukomulyo2022@gmail.com'),
                        'phone' => $setting('site.phone', '085731625435'),
                        'address' => $setting('site.address', 'Kantor Desa Sukomulyo, Indonesia'),
                    ],
                    'articles' => $this->latestArticles(),
                    'categories' => $this->newsCategories(),
                    'archiveYears' => $this->newsArchiveYears(),
                    'popularArticles' => $this->popularArticles(),
                ];
            }
        );

        $layout['articles'] = $this->normalizeArticleImages($layout['articles'] ?? []);
        $layout['popularArticles'] = $this->normalizeArticleImages($layout['popularArticles'] ?? []);

        return array_merge($layout, [
            'navigation' => $this->navigation(),
            'importedStatisticCategories' => $this->publishedStatisticCategories(),
            'publicPopulationIndicators' => $this->populationIndicators->availableIndicators(true),
        ]);
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

    /**
     * @return Collection<int, StatisticCategory>
     */
    private function publishedStatisticCategories(): Collection
    {
        if (! Schema::hasTable('statistic_categories') || ! Schema::hasTable('statistic_datasets')) {
            return collect();
        }

        return StatisticCategory::query()
            ->where('is_active', true)
            ->whereHas('datasets', fn ($query) => $query
                ->whereNotNull('statistic_import_id')
                ->where('status', 'published')
                ->whereHas('rows'))
            ->with(['datasets' => fn ($query) => $query
                ->whereNotNull('statistic_import_id')
                ->where('status', 'published')
                ->whereHas('rows')
                ->withCount('rows')
                ->orderBy('display_order')])
            ->orderBy('display_order')
            ->get();
    }

    private function navigation(): array
    {
        $statisticChildren = [
        ];
        if ($this->populationIndicators->availableIndicators(true)->isNotEmpty()) {
            $statisticChildren[] = ['label' => 'Penduduk Terkini', 'route' => 'data-statistik.population', 'active' => 'data-statistik.population'];
        }
        foreach ($this->publishedStatisticCategories() as $category) {
            $statisticChildren[] = [
                'label' => $category->name,
                'route' => 'data-statistik.detail',
                'active' => 'data-statistik.detail',
                'parameters' => ['section' => $category->slug],
            ];
        }

        return [
            ['label' => 'Beranda', 'route' => 'beranda', 'active' => 'beranda'],
            ['label' => 'Profil Desa', 'route' => 'profile-desa', 'active' => 'profile-desa*', 'children' => [
                ['label' => 'Identitas Desa', 'route' => 'profile-desa', 'active' => 'profile-desa'],
                ['label' => 'Sejarah Desa', 'route' => 'profile-desa.detail', 'active' => 'profile-desa.detail', 'parameters' => ['section' => 'sejarah']],
                ['label' => 'Visi dan Misi', 'route' => 'profile-desa.detail', 'active' => 'profile-desa.detail', 'parameters' => ['section' => 'visi-misi']],
                ['label' => 'Struktur Pemerintahan', 'route' => 'pemerintahan-desa', 'active' => 'pemerintahan-desa'],
                ['label' => 'Potensi Desa', 'route' => 'potensi-desa', 'active' => 'potensi-desa'],
            ]],
            ['label' => 'Data Statistik', 'route' => 'data-desa-statistik', 'active' => 'data-*', 'children' => $statisticChildren],
            ['label' => 'Informasi Desa', 'route' => 'informasi-publik-desa', 'active' => 'informasi-*', 'children' => [
                ['label' => 'Pengumuman Desa', 'route' => 'announcements.index', 'active' => 'announcements.*'],
                ['label' => 'APBDes', 'route' => 'transparansi-apbdes', 'active' => 'transparansi-apbdes*'],
                ['label' => 'Berita Desa', 'route' => 'berita-desa.index', 'active' => 'berita-desa.*'],
                ['label' => 'Galeri Desa', 'route' => 'galeri-desa', 'active' => 'galeri-desa'],
            ]],
            ['label' => 'Pelayanan', 'route' => 'letter-services.index', 'active' => 'pelayanan-*', 'children' => [
                ['label' => 'Syarat Administrasi', 'route' => 'informasi-desa.detail', 'active' => 'informasi-desa.detail', 'parameters' => ['section' => 'layanan-administrasi']],
                ['label' => 'Pengajuan Layanan', 'route' => 'letter-services.index', 'active' => 'letter-services.*'],
            ]],
        ];
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
            return $this->sortArticlesByDate($this->loadArticles(true));
        }

        $articles = $this->cache->remember(
            SiteCache::NEWS_LIST,
            SiteCache::TEN_MINUTES,
            fn () => $this->loadArticles()
        );
        $articles = $this->normalizeArticleImages($articles);

        $articles = $this->sortArticlesByDate($articles);
        request()->attributes->set('site.published_articles', $articles);

        return $articles;
    }

    private function sortArticlesByDate(array $articles): array
    {
        return collect($articles)
            ->sortByDesc(
                fn (array $article): string => $article['published_at']
                    ?? $article['iso_date']
                    ?? ''
            )
            ->values()
            ->all();
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
            fn (): array => range(now()->year, now()->year - 4)
        );
    }

    private function popularArticles(): array
    {
        return $this->cache->remember(
            SiteCache::NEWS_POPULAR,
            SiteCache::TEN_MINUTES,
            function (): array {
                if (Schema::hasTable('news')) {
                    $articles = News::with(['category', 'featuredImage'])
                        ->published()
                        ->orderByDesc('view_count')
                        ->latest('published_at')
                        ->limit(3)
                        ->get()
                        ->map(fn (News $article) => $this->mapArticle($article))
                        ->all();

                    if ($articles !== []) {
                        return $articles;
                    }
                }

                return collect($this->fallbackArticles())
                    ->sortByDesc('view_count')
                    ->take(3)
                    ->values()
                    ->all();
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

        $articles = $query
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get()
            ->map(
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
        $image = '/assets/village-rice-fields.jpg';
        $htmlContent = preg_replace('~https?://(?:localhost|127\.0\.0\.1)(?::\d+)?(/storage/)~i', '$1', $article->content);
        $htmlContent = $this->removeDuplicateLeadingTitle($htmlContent, $article->title);
        preg_match('~<img[^>]+src=["\']([^"\']+)["\']~i', $htmlContent, $inlineImage);

        $publishedAt = $article->published_at ?? $article->created_at;
        $detailImage = $article->featuredImage?->url ?? (isset($inlineImage[1]) ? null : $image);

        return [
            'slug' => $article->slug,
            'title' => $article->title,
            'date' => $publishedAt->locale('id')->translatedFormat('d F Y'),
            'iso_date' => $publishedAt->format('Y-m-d'),
            'year' => (string) $publishedAt->year,
            'category' => $article->category->name,
            'category_slug' => $article->category->slug,
            'image' => $article->featuredImage?->url ?? ($inlineImage[1] ?? $image),
            'medium_image' => $article->featuredImage?->medium_url ?? ($inlineImage[1] ?? $image),
            'thumbnail_image' => $article->featuredImage?->thumbnail_url ?? ($inlineImage[1] ?? $image),
            'detail_image' => $detailImage,
            'featured_image' => $article->featuredImage ? [
                'url' => $article->featuredImage->url,
                'alt' => $article->featuredImage->alt_text ?: $article->title,
            ] : null,
            'excerpt' => $article->excerpt ?? strip_tags($htmlContent),
            'seo_title' => $article->seo_title ?: $article->title,
            'seo_description' => $article->seo_description ?: ($article->excerpt ?? strip_tags($htmlContent)),
            'seo_keywords' => $article->seo_keywords,
            'status' => $article->status,
            'published_at' => $publishedAt->toIso8601String(),
            'updated_at' => $article->updated_at->toIso8601String(),
            'content' => [strip_tags($htmlContent)],
            'html_content' => $htmlContent,
            'tags' => [],
            'view_count' => (int) $article->view_count,
        ];
    }

    private function removeDuplicateLeadingTitle(string $html, string $title): string
    {
        if (! preg_match('~^\s*<(h[2-4])(?:\s[^>]*)?>(.*?)</\1>~is', $html, $heading)) {
            return $html;
        }

        $headingText = html_entity_decode(strip_tags($heading[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalize = static fn (string $text): string => mb_strtolower(
            preg_replace('/\s+/u', ' ', trim($text))
        );

        if ($normalize($headingText) !== $normalize($title)) {
            return $html;
        }

        return preg_replace('~^\s*<(h[2-4])(?:\s[^>]*)?>.*?</\1>\s*~is', '', $html, 1) ?? $html;
    }

    private function fallbackArticles(): array
    {
        $image = '/assets/village-rice-fields.jpg';

        return [
            [
                'slug' => 'musyawarah-desa-penyusunan-program-kerja',
                'title' => 'Musyawarah Desa Penyusunan Program Kerja',
                'date' => '18 Juli 2026',
                'iso_date' => '2026-07-18',
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
                'iso_date' => '2026-07-14',
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
                'iso_date' => '2026-07-09',
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
                'iso_date' => '2025-07-03',
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

    private function normalizeArticleImages(array $articles): array
    {
        return array_map(function (array $article): array {
            foreach (['image', 'medium_image', 'thumbnail_image', 'detail_image'] as $key) {
                if (array_key_exists($key, $article)) {
                    $article[$key] = $this->normalizeFallbackImage($article[$key]);
                }
            }

            return $article;
        }, $articles);
    }

    private function normalizeFallbackImage(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return $url;
        }

        return parse_url($url, PHP_URL_PATH) === '/assets/village-rice-fields.jpg'
            ? '/assets/village-rice-fields.jpg'
            : $url;
    }

    private function galleryPhotos(): array
    {
        return collect($this->galleryAlbums())
            ->flatMap(fn (array $gallery) => $gallery['photos'])
            ->values()
            ->all();
    }

    private function galleryAlbums(): array
    {
        return $this->cache->remember(
            SiteCache::GALLERY,
            SiteCache::TEN_MINUTES,
            function (): array {
                $image = '/assets/village-rice-fields.jpg';

                if (Schema::hasTable('galleries')) {
                    $galleries = Gallery::query()
                        ->where('status', 'published')
                        ->with(['items.media', 'cover'])
                        ->latest('event_date')
                        ->latest('id')
                        ->get()
                        ->map(function (Gallery $gallery) use ($image): array {
                            $galleryPhotos = $gallery->items
                                ->filter(fn ($item) => $item->media !== null)
                                ->map(fn ($item) => [
                                    'src' => $item->media->url,
                                    'title' => $gallery->title,
                                    'caption' => $this->contextualGalleryCaption(
                                        $item->caption ?? $gallery->description,
                                        $item->media->alt_text ?: $gallery->title,
                                    ),
                                ])
                                ->values()
                                ->all();

                            if ($galleryPhotos === []) {
                                $galleryPhotos[] = [
                                    'src' => $gallery->cover?->url ?? $image,
                                    'title' => $gallery->title,
                                    'caption' => $this->contextualGalleryCaption($gallery->description, $gallery->title),
                                ];
                            }

                            return [
                                'id' => $gallery->id,
                                'src' => $gallery->cover?->url ?? $galleryPhotos[0]['src'],
                                'title' => $gallery->title,
                                'caption' => $this->contextualGalleryCaption($gallery->description, $gallery->title),
                                'date' => $gallery->event_date?->translatedFormat('d F Y') ?? 'Tanggal belum ditentukan',
                                'photos' => $galleryPhotos,
                            ];
                        })
                        ->all();

                    if ($galleries !== []) {
                        return $galleries;
                    }
                }

                return [
                    ['id' => 'fallback-1', 'src' => $image, 'title' => 'Musyawarah Desa', 'caption' => 'Warga bermusyawarah untuk menyusun program desa. Pertemuan ini menjadi ruang untuk menyerap aspirasi dan menentukan prioritas pembangunan bersama.', 'date' => now()->translatedFormat('d F Y'), 'photos' => [['src' => $image, 'title' => 'Musyawarah Desa', 'caption' => 'Warga bermusyawarah untuk menyusun program desa.']]],
                    ['id' => 'fallback-2', 'src' => $image, 'title' => 'Kerja Bakti Warga', 'caption' => 'Gotong royong menjaga lingkungan tetap bersih. Warga bekerja bersama merawat fasilitas umum dan memperkuat kepedulian terhadap lingkungan sekitar.', 'date' => now()->translatedFormat('d F Y'), 'photos' => [['src' => $image, 'title' => 'Kerja Bakti Warga', 'caption' => 'Gotong royong menjaga lingkungan tetap bersih.']]],
                    ['id' => 'fallback-3', 'src' => $image, 'title' => 'Pelatihan UMKM', 'caption' => 'Peningkatan kapasitas pelaku usaha lokal. Peserta belajar mengembangkan produk dan promosi agar usaha warga semakin siap menjangkau pasar.', 'date' => now()->translatedFormat('d F Y'), 'photos' => [['src' => $image, 'title' => 'Pelatihan UMKM', 'caption' => 'Peningkatan kapasitas pelaku usaha lokal.']]],
                    ['id' => 'fallback-4', 'src' => $image, 'title' => 'Kegiatan Posyandu', 'caption' => 'Pelayanan kesehatan rutin untuk ibu dan anak. Kegiatan ini membantu keluarga memantau tumbuh kembang dan menjaga kesehatan secara berkala.', 'date' => now()->translatedFormat('d F Y'), 'photos' => [['src' => $image, 'title' => 'Kegiatan Posyandu', 'caption' => 'Pelayanan kesehatan rutin untuk ibu dan anak.']]],
                    ['id' => 'fallback-5', 'src' => $image, 'title' => 'Panen Bersama', 'caption' => 'Dokumentasi potensi pertanian Desa Sukomulyo. Hasil panen menjadi gambaran kerja keras petani sekaligus potensi ekonomi desa yang terus dikembangkan.', 'date' => now()->translatedFormat('d F Y'), 'photos' => [['src' => $image, 'title' => 'Panen Bersama', 'caption' => 'Dokumentasi potensi pertanian Desa Sukomulyo.']]],
                    ['id' => 'fallback-6', 'src' => $image, 'title' => 'Pentas Seni Desa', 'caption' => 'Ruang ekspresi seni dan budaya masyarakat. Warga menampilkan kreativitas lokal dalam suasana yang meriah dan penuh kebersamaan.', 'date' => now()->translatedFormat('d F Y'), 'photos' => [['src' => $image, 'title' => 'Pentas Seni Desa', 'caption' => 'Ruang ekspresi seni dan budaya masyarakat.']]],
                ];
            },
        );
    }

    private function contextualGalleryCaption(?string $caption, ?string $title): string
    {
        $caption = trim((string) $caption);
        $title = trim((string) $title);

        if ($caption === '' || mb_strlen($caption) >= 110) {
            return $caption !== '' ? $caption : "Dokumentasi {$title} di Desa Sukomulyo.";
        }

        $context = match (true) {
            str_contains(strtolower($title), 'musyawarah') => 'Warga berdiskusi terbuka untuk menyepakati langkah yang bermanfaat bagi kemajuan desa.',
            str_contains(strtolower($title), 'kerja bakti') => 'Kegiatan ini memperkuat semangat gotong royong dan kepedulian warga terhadap lingkungan.',
            str_contains(strtolower($title), 'umkm') => 'Pembekalan ini diharapkan membantu usaha warga tumbuh lebih kreatif dan berdaya saing.',
            str_contains(strtolower($title), 'posyandu') => 'Pelayanan dilakukan secara berkala agar keluarga mendapat pendampingan kesehatan yang mudah dijangkau.',
            str_contains(strtolower($title), 'panen') => 'Hasil kegiatan menunjukkan potensi pertanian lokal yang terus dijaga dan dikembangkan bersama.',
            str_contains(strtolower($title), 'seni') || str_contains(strtolower($title), 'festival') => 'Kegiatan ini menjadi ruang untuk merawat budaya sekaligus mempererat kebersamaan masyarakat.',
            default => 'Kegiatan ini menjadi bagian dari aktivitas warga yang mendukung kemajuan dan kebersamaan Desa Sukomulyo.',
        };

        return rtrim($caption, '.!?').'. '.$context;
    }

    private function potentialsData(): array
    {
        return [
            [
                'slug' => 'taman-merak',
                'category' => 'Wisata Alam & Perkemahan',
                'title' => 'Taman Merak Pujon',
                'description' => 'Taman Merak Pujon menawarkan suasana sejuk di antara pepohonan dengan area terbuka di tepi aliran sungai. Kawasan ini cocok untuk menikmati piknik, berkemah, outbound, dan kegiatan luar ruang bersama keluarga maupun komunitas.',
                'highlights' => ['Area perkemahan', 'Piknik dan outbound', 'Suasana hutan dan sungai'],
                'address' => 'Dusun Bakir, Desa Sukomulyo, Kecamatan Pujon, Kabupaten Malang',
                'image' => '/assets/potensi-taman-merak.webp',
                'image_alt' => 'Ilustrasi area perkemahan Taman Merak Pujon di antara hutan pinus dan aliran sungai',
                'image_caption' => 'Ilustrasi visual suasana alam Taman Merak Pujon.',
                'map_embed_url' => 'https://www.google.com/maps?q=Taman%20Merak%20Pujon%2C%20Bakir%2C%20Sukomulyo%2C%20Pujon%2C%20Malang&output=embed',
                'directions_url' => 'https://www.google.com/maps/search/?api=1&query=Taman+Merak+Pujon%2C+Bakir%2C+Sukomulyo%2C+Pujon%2C+Malang',
            ],
            [
                'slug' => 'coban-manan',
                'category' => 'Wisata Air Terjun',
                'title' => 'Coban Manan',
                'alternate_name' => 'Coban Lembah Ayu',
                'description' => 'Coban Manan, yang juga dikenal sebagai Coban Lembah Ayu, merupakan wisata air terjun bernuansa alami dengan jalur menuju lokasi yang cukup menantang. Perjalanan melewati lingkungan perkebunan, hutan, sungai kecil, dan jalan berbatu menjadi bagian dari pengalaman berkunjung.',
                'highlights' => ['Air terjun alami', 'Jalur petualangan', 'Panorama hutan dan perkebunan'],
                'address' => 'Dusun Talasan, Desa Sukomulyo, Kecamatan Pujon, Kabupaten Malang',
                'image' => '/assets/potensi-coban-manan.webp',
                'image_alt' => 'Ilustrasi Coban Manan berupa air terjun di tengah hutan tropis Desa Sukomulyo',
                'image_caption' => 'Ilustrasi visual suasana alam Coban Manan.',
                'map_embed_url' => 'https://www.google.com/maps?q=Coban%20Manan%2C%20Talasan%2C%20Sukomulyo%2C%20Pujon%2C%20Malang&output=embed',
                'directions_url' => 'https://www.google.com/maps/search/?api=1&query=Coban+Manan%2C+Talasan%2C+Sukomulyo%2C+Pujon%2C+Malang',
            ],
        ];
    }

    private function governmentOrganization(array $officials): array
    {
        $officials = collect($officials);
        $normalizedPosition = static fn (array $official): string => mb_strtolower(trim($official['position'] ?? $official['role']));
        $matches = static function (array $official, array $prefixes) use ($normalizedPosition): bool {
            $position = $normalizedPosition($official);

            foreach ($prefixes as $prefix) {
                if ($position === $prefix || str_starts_with($position, $prefix.' ')) {
                    return true;
                }
            }

            return false;
        };

        $leader = $officials->first(fn (array $official): bool => $matches($official, ['kepala desa']));
        $secretary = $officials->first(fn (array $official): bool => $matches($official, ['sekretaris desa']));
        $technicalExecutors = $officials
            ->filter(fn (array $official): bool => $matches($official, ['kasi']))
            ->values();
        $secretariatStaff = $officials
            ->filter(fn (array $official): bool => $matches($official, ['kaur']))
            ->sortBy(fn (array $official): array => [
                $secretary && (int) $official['superior_id'] === (int) $secretary['id'] ? 0 : 1,
                $official['display_order'],
            ])
            ->values();
        $hamletHeads = $officials
            ->filter(fn (array $official): bool => $matches($official, ['kasun', 'kepala dusun']))
            ->values();

        $groupedIds = collect([$leader, $secretary])
            ->filter()
            ->concat($technicalExecutors)
            ->concat($secretariatStaff)
            ->concat($hamletHeads)
            ->pluck('id')
            ->all();

        return [
            'leader' => $leader,
            'technicalExecutors' => $technicalExecutors->all(),
            'secretary' => $secretary,
            'secretariatStaff' => $secretariatStaff->all(),
            'hamletHeads' => $hamletHeads->all(),
            'others' => $officials->reject(fn (array $official): bool => in_array($official['id'], $groupedIds, true))->values()->all(),
        ];
    }

    private function fallbackGovernmentOfficials(): array
    {
        $officials = [
            ['name' => 'Safiul Anwar, ST', 'position' => 'Kepala Desa', 'superior_id' => null],
            ['name' => 'Angga Saputra', 'position' => 'Kasi Pemerintahan', 'superior_id' => 1],
            ['name' => 'Wike Priharti Y', 'position' => 'Kasi Pelayanan', 'superior_id' => 1],
            ['name' => 'Mohamad Sholeh', 'position' => 'Kasi Kesejahteraan', 'superior_id' => 1],
            ['name' => 'Baktiyar Kufain', 'position' => 'Sekretaris Desa', 'superior_id' => 1],
            ['name' => 'Suwarno', 'position' => 'Kaur Keuangan', 'superior_id' => 5],
            ['name' => 'Reza Tri Purnomo', 'position' => 'Kaur Perencanaan', 'superior_id' => 5],
            ['name' => 'Catur Yulianto', 'position' => 'Kaur Tata Usaha dan Umum', 'superior_id' => 5],
            ['name' => 'Bambang S', 'position' => 'Kasun Bakir', 'superior_id' => 1],
            ['name' => 'Sispanaji', 'position' => 'Kasun Biyan', 'superior_id' => 1],
            ['name' => 'Nikita F Z', 'position' => 'Kasun Gumul', 'superior_id' => 1],
            ['name' => 'Fendi Priyo S', 'position' => 'Kasun Kedungrejo', 'superior_id' => 1],
            ['name' => 'Cahyo Utomo', 'position' => 'Kasun Talasan', 'superior_id' => 1],
        ];

        return collect($officials)->map(function (array $official, int $index): array {
            $id = $index + 1;
            $name = $official['name'];

            return [
                'id' => $id,
                'role' => $official['position'],
                'position' => $official['position'],
                'name' => $name,
                'photo' => $this->officialAssetPhoto($name),
                'photo_alt' => "{$name} - {$official['position']}",
                'initials' => $this->officialInitials($name),
                'superior_id' => $official['superior_id'],
                'display_order' => $id,
            ];
        })->all();
    }

    private function officialAssetPhoto(string $name): ?string
    {
        return [
            'safiul anwar, st' => '/assets/safiul-anwar.jpeg',
            'angga saputra' => '/assets/angga-saputra.jpeg',
            'wike priharti y' => '/assets/wike-priharti-y.jpeg',
            'mohamad sholeh' => '/assets/muhammad-sholeh.jpeg',
            'suwarno' => '/assets/suwarno.jpeg',
            'reza tri purnomo' => '/assets/reza-tri.jpeg',
            'catur yulianto' => '/assets/catur-yulianto.jpeg',
            'bambang s' => '/assets/bambang.jpeg',
            'sispanaji' => '/assets/sispanaji.jpeg',
            'nikita f z' => '/assets/nikita.jpeg',
            'fendi priyo s' => '/assets/fendi-priyo.jpeg',
        ][mb_strtolower(trim($name))] ?? null;
    }

    private function officialInitials(string $name): string
    {
        return collect(preg_split('/[\s,]+/u', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }

    private function profilePages(): array
    {
        return [
            'identitas' => [
                'title' => 'Identitas Desa',
                'url' => route('profile-desa'),
                'comments_url' => route('profile-desa.comments'),
            ],
            'sejarah' => [
                'title' => 'Sejarah Desa',
                'url' => route('profile-desa.detail', ['section' => 'sejarah']),
                'comments_url' => route('profile-desa.section-comments', ['section' => 'sejarah']),
            ],
            'visi-misi' => [
                'title' => 'Visi dan Misi',
                'url' => route('profile-desa.detail', ['section' => 'visi-misi']),
                'comments_url' => route('profile-desa.section-comments', ['section' => 'visi-misi']),
            ],
            'struktur-pemerintahan' => [
                'title' => 'Struktur Pemerintahan',
                'url' => route('pemerintahan-desa'),
                'comments_url' => route('profile-desa.section-comments', ['section' => 'struktur-pemerintahan']),
            ],
            'potensi-desa' => [
                'title' => 'Potensi Desa',
                'url' => route('potensi-desa'),
                'comments_url' => route('profile-desa.section-comments', ['section' => 'potensi-desa']),
            ],
        ];
    }

    private function profilePageData(string $pageKey): array
    {
        $page = $this->profilePages()[$pageKey];
        $commentsQuery = Schema::hasTable('village_comments')
            ? VillageComment::query()->where('page_key', $pageKey)->where('status', 'approved')->where('is_visible', true)
            : null;

        return [
            'villageLeader' => $this->villageLeader(),
            'villageRegulations' => $this->villageRegulations(),
            'latestComments' => $commentsQuery ? (clone $commentsQuery)->latest()->limit(3)->get() : collect(),
            'commentContext' => [
                'page_key' => $pageKey,
                'title' => $page['title'],
                'url' => $page['url'],
                'comments_url' => $page['comments_url'],
                'count' => $commentsQuery ? (clone $commentsQuery)->count() : 0,
            ],
        ];
    }

    private function villageLeader(): array
    {
        $leader = Schema::hasTable('officials')
            ? Official::with('photo')->where('position', 'Kepala Desa')->where('is_active', true)->orderBy('display_order')->first()
            : null;

        return [
            'name' => $leader?->full_name ?: 'Kepala Desa Sukomulyo',
            'role' => $leader?->position_label ?: 'Kepala Desa',
            'photo' => $leader?->photo?->url,
            'photo_alt' => $leader?->photo?->alt_text ?: ($leader?->full_name ?: 'Kepala Desa Sukomulyo'),
            'greeting' => trim(strip_tags((string) $leader?->biography))
                ?: 'Assalamu’alaikum Warahmatullahi Wabarakatuh. Selamat datang di website resmi Desa Sukomulyo. Semoga layanan informasi ini mendekatkan pemerintah desa dengan seluruh masyarakat.',
        ];
    }

    private function villageRegulations(): array
    {
        if (! Schema::hasTable('publications') || ! Schema::hasTable('publication_attachments')) {
            return [];
        }

        return Publication::query()
            ->published()
            ->with(['attachments.media'])
            ->where('type', 'regulation')
            ->whereHas('attachments.media', fn ($query) => $query->where('mime_type', 'application/pdf'))
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(function (Publication $publication): array {
                $attachment = $publication->attachments
                    ->first(fn ($item) => $item->media && $item->media->mime_type === 'application/pdf');

                return [
                    'title' => $publication->title,
                    'number' => 'Peraturan Desa',
                    'year' => (string) ($publication->published_at?->year ?? $publication->start_date?->year ?? now()->year),
                    'url' => $attachment?->media?->url,
                ];
            })
            ->filter(fn (array $regulation) => filled($regulation['url']))
            ->values()
            ->all();
    }
}
