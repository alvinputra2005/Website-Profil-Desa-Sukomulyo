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
                <h2>Mengenal Desa Sukomulyo</h2>
                <p>Desa Sukomulyo tumbuh melalui semangat gotong royong yang kuat, pelayanan publik yang terbuka dan responsif, serta pengembangan potensi masyarakat secara berkelanjutan. Dengan dukungan warga dan pemerintah desa, Sukomulyo terus berupaya mewujudkan lingkungan yang maju, mandiri, nyaman, dan sejahtera bagi seluruh masyarakat.</p>
                <a class="learnmore" href="{{ route('profile-desa') }}">Lihat Profile Desa <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="aboutus_thumbox">
                <img src="{{ asset('assets/village-rice-fields.jpg') }}" alt="Hamparan persawahan hijau dengan latar pegunungan saat matahari terbenam">
            </div>
        </div>
    </section>
    </div>

    <section class="budget-section" aria-labelledby="budget-title" data-budget-section>
        <div class="container">
            <header class="home-section-heading budget-heading">
                <h2 id="budget-title">Transparansi APBDes</h2>
                <a class="home-section-link" href="{{ route('transparansi-apbdes') }}">Lihat selengkapnya <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
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

        </div>
    </section>

    <section class="latest-news-section">
        <div class="container">
            <header class="home-section-heading">
                <div>
                    <h2 class="section-title">Berita Terbaru</h2>
                </div>
                <a class="home-section-link" href="{{ route('berita-desa.index') }}">Lihat selengkapnya <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </header>

            <nav class="home-news-categories" aria-label="Kategori berita desa">
                <button class="home-news-category is-active" type="button" data-news-filter="all" aria-pressed="true" aria-controls="home-news-grid">
                    Terbaru <i class="fas fa-check" aria-hidden="true"></i>
                </button>
                @foreach ($categories as $category)
                    <button class="home-news-category" type="button" data-news-filter="{{ $category['category_slug'] }}" aria-pressed="false" aria-controls="home-news-grid">
                        {{ $category['category'] }} <i class="fas fa-check" aria-hidden="true"></i>
                    </button>
                @endforeach
            </nav>

            <div class="latest-news-grid" id="home-news-grid">
                @foreach (array_slice($articles, 0, 4) as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-gallery-section" aria-labelledby="home-gallery-title">
        <div class="container">
            <header class="home-section-heading">
                <div>
                    <h2 class="section-title" id="home-gallery-title">Galeri Desa</h2>
                </div>
                <a class="home-section-link" href="{{ route('galeri-desa') }}">Lihat selengkapnya <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            </header>

            <div class="home-gallery-grid">
                @foreach ($galleryPhotos as $photo)
                    <button class="gallery-item" type="button" data-gallery-item data-image="{{ $photo['src'] }}" data-title="{{ $photo['title'] }}" data-caption="{{ $photo['caption'] }}">
                        <img src="{{ $photo['src'] }}" alt="{{ $photo['title'] }}" loading="lazy" decoding="async">
                        <span class="gallery-overlay"><i class="fas fa-search-plus" aria-hidden="true"></i><strong>{{ $photo['title'] }}</strong></span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-map-section" aria-labelledby="home-map-title">
        <div class="container">
            <header class="home-section-heading">
                <div>
                    <h2 class="section-title" id="home-map-title">Peta Desa Sukomulyo</h2>
                </div>
            </header>

            <div class="map-layout">
                <div class="map-frame">
                    <iframe
                        src="https://www.google.com/maps?q=Desa%20Sukomulyo&output=embed"
                        title="Peta lokasi Desa Sukomulyo"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen>
                    </iframe>
                </div>

                <aside class="map-information">
                    <span class="section-kicker">Lokasi Desa</span>
                    <h2>{{ $site['name'] }}</h2>
                    <p>Peta membantu masyarakat menemukan kantor desa dan mengenali posisi wilayah Desa Sukomulyo.</p>
                    <ul>
                        <li><i class="fas fa-map-marker-alt" aria-hidden="true"></i><span><strong>Alamat Kantor Desa</strong>{{ $site['address'] }}</span></li>
                        <li><i class="fas fa-phone" aria-hidden="true"></i><span><strong>Telepon</strong>{{ $site['phone'] }}</span></li>
                        <li><i class="fas fa-envelope" aria-hidden="true"></i><span><strong>Email</strong>{{ $site['email'] }}</span></li>
                    </ul>
                    <a class="learnmore" href="https://www.google.com/maps/search/?api=1&query=Desa+Sukomulyo" target="_blank" rel="noopener noreferrer">Buka di Google Maps</a>
                </aside>
            </div>
        </div>
    </section>

    <dialog class="gallery-dialog" data-gallery-dialog aria-labelledby="gallery-dialog-title">
        <button class="dialog-close" type="button" data-gallery-close aria-label="Tutup galeri"><i class="fas fa-times" aria-hidden="true"></i></button>
        <img data-gallery-image src="" alt="">
        <div class="dialog-caption">
            <h2 id="gallery-dialog-title" data-gallery-title></h2>
            <p data-gallery-caption></p>
        </div>
    </dialog>
</x-layouts.app>
