<x-layouts.app title="Wilayah Desa" description="Temukan lokasi dan informasi wilayah Desa Sukomulyo.">
    <x-page-header title="Wilayah Desa" description="Temukan lokasi dan informasi wilayah Desa Sukomulyo." :breadcrumbs="[
        ['label' => 'Profile Desa', 'url' => route('profile-desa')],
        ['label' => 'Wilayah Desa'],
    ]" />

    @php
        $shareUrl = route('peta-desa');
        $shareText = 'Wilayah Desa Sukomulyo - '.$site['name'];
    @endphp

    <div class="container news-detail-container profile-detail-container">
        <div id="sc_innerpage_wrap" class="news-detail-layout profile-article-layout" data-hide-back-to-top data-disable-scroll-reveal>
            <article class="sc_innerpage_contentbx single-article village-profile-article">
                <header class="entry-header">
                    <h1 class="entry-title">Wilayah Desa Sukomulyo</h1>
                    <div class="postmeta" aria-label="Informasi artikel">
                        <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>Diperbarui {{ now()->translatedFormat('d F Y') }}</span>
                        <span class="post-author"><i class="far fa-user" aria-hidden="true"></i>Pemerintah Desa Sukomulyo</span>
                        <button class="profile-print-button" type="button" data-print-article>
                            <i class="fas fa-print" aria-hidden="true"></i>Cetak Artikel
                        </button>
                    </div>
                </header>

                <div class="article-reading-body">
                    <div class="entry-content">
                        <p>Informasi wilayah membantu masyarakat mengenali lokasi desa, menjangkau kantor pelayanan, dan memperoleh saluran komunikasi resmi Pemerintah Desa Sukomulyo.</p>

                        <h2>Peta Desa Sukomulyo</h2>
                        <div class="map-layout profile-map-layout">
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

                        <p class="data-note"><i class="fas fa-info-circle" aria-hidden="true"></i> Titik koordinat dapat diperbarui setelah koordinat resmi kantor Desa Sukomulyo tersedia.</p>
                    </div>
                </div>

                <x-profile-comment-section :context="$commentContext" />
            </article>

            <x-profile-share :url="$shareUrl" :text="$shareText" label="artikel Wilayah Desa" />
            <x-profile-sidebar :leader="$villageLeader" :regulations="$villageRegulations" :latest-comments="$latestComments" />
        </div>
    </div>
</x-layouts.app>
