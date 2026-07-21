<x-layouts.app description="Portal informasi resmi Pemerintah Desa Sukomulyo.">
    <x-hero-slider :articles="$articles" />

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
