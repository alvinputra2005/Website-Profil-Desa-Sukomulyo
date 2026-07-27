<x-layouts.app title="Potensi Desa" description="Sumber daya dan kekuatan lokal yang mendukung kemajuan masyarakat.">
    <x-page-header title="Potensi Desa" description="Sumber daya dan kekuatan lokal yang mendukung kemajuan masyarakat." :breadcrumbs="[
        ['label' => 'Profile Desa', 'url' => route('profile-desa')],
        ['label' => 'Potensi Desa'],
    ]" />

    @php
        $shareUrl = route('potensi-desa');
        $shareText = 'Potensi Desa Sukomulyo - '.$site['name'];
    @endphp

    <div class="container news-detail-container profile-detail-container">
        <div id="sc_innerpage_wrap" class="news-detail-layout profile-article-layout" data-hide-back-to-top data-disable-scroll-reveal>
            <article class="sc_innerpage_contentbx single-article village-profile-article">
                <header class="entry-header">
                    <h1 class="entry-title">Potensi Desa Sukomulyo</h1>
                    <div class="postmeta" aria-label="Informasi artikel">
                        <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>Diperbarui {{ now()->translatedFormat('d F Y') }}</span>
                        <span class="post-author"><i class="far fa-user" aria-hidden="true"></i>Pemerintah Desa Sukomulyo</span>
                        <button class="profile-print-button" type="button" data-print-article>
                            <i class="fas fa-print" aria-hidden="true"></i>Cetak Artikel
                        </button>
                    </div>
                </header>

                <figure class="news-detail-hero profile-detail-hero">
                    <img src="{{ asset('assets/village-rice-fields.jpg') }}" alt="Potensi alam dan pertanian Desa Sukomulyo">
                    <figcaption>Sumber daya lokal yang menjadi kekuatan pembangunan Desa Sukomulyo.</figcaption>
                </figure>

                <div class="article-reading-body">
                    <div class="entry-content">
                        <p>Desa Sukomulyo memiliki beragam sumber daya alam, usaha warga, tradisi, dan lingkungan yang dapat dikembangkan secara berkelanjutan untuk meningkatkan kesejahteraan masyarakat.</p>

                        <div class="potential-grid profile-potential-grid">
                            @foreach ($potentials as $potential)
                                <article class="potential-card">
                                    <div class="potential-image">
                                        <img src="{{ $potential['image'] }}" alt="{{ $potential['title'] }}">
                                        <span><i class="{{ $potential['icon'] }}" aria-hidden="true"></i></span>
                                    </div>
                                    <div class="potential-body">
                                        <h2>{{ $potential['title'] }}</h2>
                                        <p>{{ $potential['description'] }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <aside class="callout">
                            <div>
                                <h2>Punya produk atau potensi yang ingin ditampilkan?</h2>
                                <p>Sampaikan informasi kepada pemerintah desa agar dapat diverifikasi dan dipublikasikan.</p>
                            </div>
                            <a class="learnmore" href="{{ route('kontak.index') }}">Hubungi Kami</a>
                        </aside>
                    </div>
                </div>

                <x-profile-comment-section :context="$commentContext" />
            </article>

            <x-profile-share :url="$shareUrl" :text="$shareText" label="artikel Potensi Desa" />
            <x-profile-sidebar :leader="$villageLeader" :regulations="$villageRegulations" :latest-comments="$latestComments" />
        </div>
    </div>
</x-layouts.app>
