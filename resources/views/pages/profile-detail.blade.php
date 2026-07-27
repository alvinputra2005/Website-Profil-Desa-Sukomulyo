<x-layouts.app :title="$page['title']" :description="$page['description']">
    <x-page-header :title="$page['title']" :description="$page['description']" :breadcrumbs="[
        ['label' => 'Profile Desa', 'url' => route('profile-desa')],
        ['label' => $page['title']],
    ]" />

    @php
        $shareUrl = $commentContext['url'];
        $shareText = $page['title'].' Desa Sukomulyo - '.$site['name'];
    @endphp

    <div class="container news-detail-container profile-detail-container">
        <div id="sc_innerpage_wrap" class="news-detail-layout profile-article-layout" data-hide-back-to-top data-disable-scroll-reveal>
            <article class="sc_innerpage_contentbx single-article village-profile-article">
                <header class="entry-header">
                    <h1 class="entry-title">{{ $page['title'] }}</h1>
                    <div class="postmeta" aria-label="Informasi artikel">
                        <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>Diperbarui {{ now()->translatedFormat('d F Y') }}</span>
                        <span class="post-author"><i class="far fa-user" aria-hidden="true"></i>Pemerintah Desa Sukomulyo</span>
                        <button class="profile-print-button" type="button" data-print-article>
                            <i class="fas fa-print" aria-hidden="true"></i>Cetak Artikel
                        </button>
                    </div>
                </header>

                <figure class="news-detail-hero profile-detail-hero">
                    <img src="{{ asset('assets/village-rice-fields.jpg') }}" alt="{{ $page['title'] }} Desa Sukomulyo">
                    <figcaption>{{ $page['description'] }}</figcaption>
                </figure>

                <div class="article-reading-body">
                    <div class="entry-content">
                        @forelse ($sections as $section)
                            <section class="profile-article-section">
                                <h2>{{ $section->title }}</h2>
                                @if ($section->image)
                                    <img class="profile-section-image" src="{{ $section->image->url }}" alt="{{ $section->image->alt_text ?: $section->title }}">
                                @endif
                                <div>{!! $section->content !!}</div>
                            </section>
                        @empty
                            @foreach ($page['fallback'] as $section)
                                <section class="profile-article-section">
                                    <h2>{{ $section['title'] }}</h2>
                                    <p>{{ $section['content'] }}</p>
                                </section>
                            @endforeach
                        @endforelse
                    </div>
                </div>

                <x-profile-comment-section :context="$commentContext" />
            </article>

            <x-profile-share :url="$shareUrl" :text="$shareText" :label="'artikel '.$page['title']" />
            <x-profile-sidebar :leader="$villageLeader" :regulations="$villageRegulations" :latest-comments="$latestComments" />
        </div>
    </div>
</x-layouts.app>
