<x-layouts.app
    title="Potensi Desa"
    description="Jelajahi Taman Merak Pujon dan Coban Manan, dua potensi wisata alam Desa Sukomulyo."
    :og-image="asset('assets/potensi-taman-merak.webp')"
>
    <x-page-header title="Potensi Desa" description="Jelajahi wisata alam yang menjadi daya tarik Desa Sukomulyo." :breadcrumbs="[
        ['label' => 'Profile Desa', 'url' => route('profile-desa')],
        ['label' => 'Potensi Desa'],
    ]" />

    <div class="container news-detail-container profile-detail-container">
        <div id="sc_innerpage_wrap" class="news-detail-layout profile-article-layout profile-article-layout--no-sidebar" data-hide-back-to-top data-disable-scroll-reveal>
            <article class="sc_innerpage_contentbx single-article village-profile-article">
                <header class="entry-header village-potential-intro">
                    <h1 class="entry-title">Potensi Desa Sukomulyo</h1>
                    <div class="postmeta" aria-label="Informasi artikel">
                        <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>Diperbarui {{ now()->translatedFormat('d F Y') }}</span>
                        <span class="post-author"><i class="far fa-user" aria-hidden="true"></i>Pemerintah Desa Sukomulyo</span>
                    </div>
                </header>

                <div class="article-reading-body village-tourism-list">
                    @foreach ($potentials as $potential)
                        @php
                            $isReversed = $loop->even;
                            $imagePanelId = 'potensi-'.$potential['slug'].'-gambar';
                        @endphp

                        <section
                            id="{{ $potential['slug'] }}"
                            class="village-tourism-section {{ $isReversed ? 'village-tourism-section--reversed' : '' }}"
                            aria-labelledby="{{ $potential['slug'] }}-title"
                        >
                            <div class="village-tourism-copy">
                                <h2 id="{{ $potential['slug'] }}-title">{{ $potential['title'] }}</h2>
                                @if (! empty($potential['alternate_name']))
                                    <p class="village-tourism-alias">Dikenal juga sebagai {{ $potential['alternate_name'] }}</p>
                                @endif
                                <p>{{ $potential['description'] }}</p>
                            </div>

                            <div class="village-tourism-accordion">
                                <section class="village-tourism-disclosure village-tourism-disclosure--static-image">
                                    <div class="village-tourism-static-toggle" aria-hidden="true">
                                        <span><i class="far fa-image" aria-hidden="true"></i>Gambar {{ $potential['title'] }}</span>
                                    </div>
                                    <div id="{{ $imagePanelId }}" class="village-tourism-panel village-tourism-panel--image">
                                        <figure>
                                            <div class="village-tourism-image-media">
                                                <img src="{{ asset(ltrim($potential['image'], '/')) }}" alt="{{ $potential['image_alt'] }}" loading="lazy">
                                                <a
                                                    class="village-tourism-location-below"
                                                    href="{{ $potential['directions_url'] }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    aria-label="Lihat lokasi {{ $potential['title'] }} di Google Maps"
                                                >
                                                    <span>{{ $potential['address'] }}</span>
                                                    <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </figure>
                                    </div>
                                </section>
                            </div>
                        </section>
                    @endforeach
                </div>

                <x-profile-comment-section :context="$commentContext" />
            </article>

        </div>
    </div>
</x-layouts.app>
