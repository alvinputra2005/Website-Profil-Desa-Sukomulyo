<x-layouts.app title="Struktur Pemerintahan" description="Struktur organisasi dan prinsip pelayanan Pemerintah Desa Sukomulyo.">
    <x-page-header title="Struktur Pemerintahan" description="Struktur organisasi dan prinsip pelayanan Pemerintah Desa Sukomulyo." :breadcrumbs="[
        ['label' => 'Profile Desa', 'url' => route('profile-desa')],
        ['label' => 'Struktur Pemerintahan'],
    ]" />

    @php
        $shareUrl = route('pemerintahan-desa');
        $shareText = 'Struktur Pemerintahan Desa Sukomulyo - '.$site['name'];
    @endphp

    <div class="container news-detail-container profile-detail-container">
        <div id="sc_innerpage_wrap" class="news-detail-layout profile-article-layout" data-hide-back-to-top data-disable-scroll-reveal>
            <section class="sc_innerpage_contentbx single-article village-profile-article village-government-content">
                <div class="content-intro">
                    <span class="section-kicker">Struktur Organisasi</span>
                    <h1>Perangkat Desa Sukomulyo</h1>
                    <p>Pemerintah desa menjalankan pelayanan, administrasi, pembangunan, dan pemberdayaan masyarakat sesuai tugas masing-masing.</p>
                </div>

                <div class="official-grid">
                    @foreach ($officials as $official)
                        <article class="official-card">
                            <div class="official-avatar">
                                @if($official['photo'])
                                    <img src="{{ $official['photo'] }}" alt="{{ $official['photo_alt'] ?: $official['name'] }}">
                                @else
                                    <i class="fas fa-user" aria-hidden="true"></i>
                                @endif
                            </div>
                            <div>
                                <p>{{ $official['role'] }}</p>
                                <h2>{{ $official['name'] }}</h2>
                            </div>
                        </article>
                    @endforeach
                </div>

                <section class="service-values">
                    <h2>Komitmen Pelayanan</h2>
                    <div class="value-grid">
                        <article><i class="fas fa-handshake" aria-hidden="true"></i><h3>Melayani</h3><p>Mendahulukan kebutuhan masyarakat dengan ramah dan responsif.</p></article>
                        <article><i class="fas fa-eye" aria-hidden="true"></i><h3>Transparan</h3><p>Menyampaikan program dan informasi publik secara terbuka.</p></article>
                        <article><i class="fas fa-balance-scale" aria-hidden="true"></i><h3>Akuntabel</h3><p>Menjalankan tugas dengan tertib dan dapat dipertanggungjawabkan.</p></article>
                    </div>
                </section>

                <x-profile-comment-section :context="$commentContext" />
            </section>

            <x-profile-share :url="$shareUrl" :text="$shareText" label="Struktur Pemerintahan" />
            <x-profile-sidebar :leader="$villageLeader" :regulations="$villageRegulations" :latest-comments="$latestComments" />
        </div>
    </div>
</x-layouts.app>
