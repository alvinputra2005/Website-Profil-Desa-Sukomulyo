<x-layouts.app description="Portal informasi resmi Pemerintah Desa Sukomulyo.">
    <x-hero-slider :articles="$articles" />

    <div class="home-stats-about-flow">
    <section class="village-stats-section" aria-labelledby="village-stats-title" data-village-stats>
        <div class="container village-stats-layout">
            <div class="village-stats-intro">
                <h2 id="village-stats-title">Statistik <span>Desa</span></h2>
                <p>Ringkasan data kependudukan, keluarga, pendidikan, dan pekerjaan Desa Sukomulyo.</p>
                <a href="{{ route('data-desa-statistik') }}">Lihat data selengkapnya <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>

            <div class="village-population" aria-label="Jumlah penduduk berdasarkan jenis kelamin">
                <div class="population-profiles">
                    @foreach ($populationByGender as $population)
                        <article class="population-profile">
                            <img src="{{ asset($population['image']) }}" alt="Ilustrasi {{ strtolower($population['label']) }}">
                            <strong data-stat-count="{{ $population['value'] }}">{{ number_format($population['value'], 0, ',', '.') }}</strong>
                            <span>{{ $population['label'] }}</span>
                        </article>
                    @endforeach
                </div>
                <p class="population-total">Total <strong data-stat-count="{{ array_sum(array_column($populationByGender, 'value')) }}">{{ number_format(array_sum(array_column($populationByGender, 'value')), 0, ',', '.') }}</strong> penduduk</p>
            </div>

            <div class="village-stats-grid">
                @foreach ($villageStatistics as $statistic)
                    <article class="village-stat-card">
                        <span class="village-stat-icon"><i class="{{ $statistic['icon'] }}" aria-hidden="true"></i></span>
                        <div class="village-stat-value">
                            <strong data-stat-count="{{ $statistic['value'] }}">{{ number_format($statistic['value'], 0, ',', '.') }}</strong>
                            <span>{{ $statistic['unit'] }}</span>
                        </div>
                        <h3>{{ $statistic['label'] }}</h3>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="about_panel">
        <div class="container about-grid">
            <div class="aboutus_contentcol">
                <span class="section-kicker">Tentang Kami</span>
                <h2>Mengenal Desa Sukomulyo</h2>
                <p>Desa Sukomulyo tumbuh melalui semangat gotong royong, pelayanan publik yang terbuka, serta pengembangan potensi masyarakat secara berkelanjutan.</p>
                <p>Website ini menjadi ruang informasi bersama agar warga lebih mudah mengetahui program, kegiatan, dan layanan pemerintah desa.</p>
                <a class="learnmore" href="{{ route('profile-desa') }}">Baca Profile Desa</a>
            </div>
            <div class="aboutus_thumbox">
                <img src="{{ asset('assets/village-rice-fields.jpg') }}" alt="Persawahan dan permukiman desa di Indonesia">
            </div>
        </div>
    </section>
    </div>

    <section class="budget-section" aria-labelledby="budget-title" data-budget-section>
        <div class="container">
            <header class="budget-heading">
                <h2 id="budget-title">TRANSPARANSI APBDES</h2>
            </header>

            <div class="budget-grid">
                @foreach ($apbdes['panels'] as $panel)
                    <div class="budget-panel">
                        <article class="budget-card">
                            <header class="budget-card-header">
                                <h3>{{ $panel['title'] }}</h3>
                            </header>
                            <div class="budget-card-scroll" tabindex="0" aria-label="Rincian {{ $panel['title'] }}">
                                <ul class="budget-list">
                                    @foreach ($panel['items'] as $item)
                                        @php($percentage = $item['percentage'] ?? round(($item['value'] / $panel['total']) * 100))
                                        <li>
                                            <h4>{{ $item['label'] }}</h4>
                                            <p>Rp {{ number_format($item['value'], 2, ',', '.') }}</p>
                                            <div class="budget-progress" role="progressbar" aria-label="{{ $item['label'] }} {{ $percentage }} persen" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percentage }}">
                                                <span class="budget-progress-fill" style="--budget-value: {{ $percentage }}%; --budget-delay: {{ $loop->index * 100 }}ms" aria-hidden="true">
                                                    <span class="budget-progress-percent">{{ $percentage }}%</span>
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </article>

                        <div class="budget-total">
                            <strong>{{ $panel['total_label'] }} ({{ $panel['total_percentage'] }}%)</strong>
                            <p>Rp {{ number_format($panel['total'], 2, ',', '.') }}</p>
                            <div class="budget-progress" role="progressbar" aria-label="{{ $panel['total_label'] }} {{ $panel['total_percentage'] }} persen" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $panel['total_percentage'] }}">
                                <span class="budget-progress-fill" style="--budget-value: {{ $panel['total_percentage'] }}%; --budget-delay: {{ count($panel['items']) * 100 }}ms" aria-hidden="true">
                                    <span class="budget-progress-percent">{{ $panel['total_percentage'] }}%</span>
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="section-action budget-section-action">
                <a class="button" href="{{ route('transparansi-apbdes') }}">Lihat selengkapnya</a>
            </div>
        </div>
    </section>

    <section class="ourmission_wrapper">
        <div class="container">
            <span class="section-kicker">Jelajahi Desa</span>
            <h2 class="section-title">Informasi dan Layanan Desa</h2>
            <p class="shortdesc">Akses cepat ke informasi pemerintahan, layanan publik, potensi, berita, dan dokumentasi kegiatan Desa Sukomulyo.</p>
            <div class="mission-grid">
                @foreach ($missions as $mission)
                    <a class="mission-card" href="{{ route($mission['route']) }}">
                        <span class="mission-icon"><i class="{{ $mission['icon'] }}" aria-hidden="true"></i></span>
                        <h3>{{ $mission['title'] }}</h3>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section id="fivebx_services_panel">
        <div class="container">
            <span class="section-kicker">Unggulan</span>
            <h2 class="section-title">Potensi Desa</h2>
            <div class="potential-feature-grid">
                @foreach ($featuredPotentials as $potential)
                    <article class="potential-feature">
                        <div class="potential-feature-content">
                            <i class="{{ $potential['icon'] }}" aria-hidden="true"></i>
                            <h3>{{ $potential['title'] }}</h3>
                            <p>{{ $potential['description'] }}</p>
                            <a class="pagereadmore" href="{{ route('data-desa-statistik') }}">Lihat Data Desa</a>
                        </div>
                        <img src="{{ $potential['image'] }}" alt="{{ $potential['title'] }}">
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="latest-news-section">
        <div class="container">
            <span class="section-kicker">Informasi Terkini</span>
            <h2 class="section-title">Berita Terbaru</h2>
            <div class="latest-news-grid">
                @foreach (array_slice($articles, 0, 3) as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
            <div class="section-action"><a class="button" href="{{ route('berita-desa.index') }}">Lihat Semua Berita</a></div>
        </div>
    </section>
</x-layouts.app>
