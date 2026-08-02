<x-layouts.app
    title="Identitas Desa"
    :description="'Informasi lengkap identitas, wilayah, pemerintahan, dan karakter '.$site['name'].'.'">
    <x-page-header title="Identitas Desa" :breadcrumbs="[
        ['label' => 'Profile Desa'],
        ['label' => 'Identitas Desa'],
    ]" />

    @php
        $shareUrl = route('profile-desa');
        $shareText = 'Identitas Desa Sukomulyo - '.$site['name'];
        $profileSection = $profileSections->firstWhere('section_key', 'profile');
        $profileUpdatedAt = $profileSection?->updated_at ?? now();
        $profileHero = $profileSection?->image?->url ?? asset('assets/village-rice-fields.jpg');
        $profileHeroAlt = $profileSection?->image?->alt_text ?: 'Pemandangan wilayah Desa Sukomulyo';
    @endphp

    <div class="container news-detail-container profile-detail-container">
        <div id="sc_innerpage_wrap" class="news-detail-layout profile-article-layout" data-hide-back-to-top data-disable-scroll-reveal>
            <article class="sc_innerpage_contentbx single-article village-profile-article">
                <header class="entry-header">
                    <h1 class="entry-title">Identitas Desa Sukomulyo</h1>
                    <div class="postmeta" aria-label="Informasi artikel">
                        <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>Diperbarui {{ $profileUpdatedAt->translatedFormat('d F Y') }}</span>
                        <span class="post-author"><i class="far fa-user" aria-hidden="true"></i>Pemerintah Desa Sukomulyo</span>
                        <button class="profile-print-button" type="button" data-print-article>
                            <i class="fas fa-print" aria-hidden="true"></i>Cetak Artikel
                        </button>
                    </div>
                </header>

                <figure class="news-detail-hero profile-detail-hero">
                    <img src="{{ $profileHero }}" alt="{{ $profileHeroAlt }}">
                    <figcaption>Gambaran wilayah dan kehidupan masyarakat Desa Sukomulyo.</figcaption>
                </figure>

                <div class="article-reading-body">
                    <div class="entry-content profile-overview-content">
                        @if($profileSection)
                            <section class="profile-article-section profile-article-section--overview" data-profile-section="profile">
                                <h2>{{ $profileSection->title }}</h2>
                                <div class="profile-cms-content">{!! $profileSection->content !!}</div>
                            </section>
                        @else
                            <section class="profile-article-section profile-article-section--overview">
                                <h2>Gambaran Umum Desa Sukomulyo</h2>
                                <p>Informasi profil desa sedang diperbarui.</p>
                            </section>
                        @endif
                    </div>
                </div>

                <x-profile-comment-section :context="$commentContext" />
            </article>

            <x-profile-share :url="$shareUrl" :text="$shareText" label="artikel Identitas Desa" />

            <x-profile-sidebar :leader="$villageLeader" :regulations="$villageRegulations" :latest-comments="$latestComments" />
        </div>
    </div>
</x-layouts.app>
