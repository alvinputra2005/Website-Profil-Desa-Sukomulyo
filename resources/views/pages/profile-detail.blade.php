<x-layouts.app :title="$page['title']" :description="$page['description']">
    <x-page-header :title="$page['title']" :description="$page['description']" :breadcrumbs="[
        ['label' => 'Profile Desa', 'url' => route('profile-desa')],
        ['label' => $page['title']],
    ]" />

    @php
        $shareUrl = $commentContext['url'];
        $shareText = $page['title'].' Desa Sukomulyo - '.$site['name'];
        $detailUpdatedAt = $sections->max('updated_at') ?? now();
        $detailHero = asset('assets/balai-desa-sukomulyo.jpeg');
        $detailHeroAlt = 'Balai Desa Sukomulyo - '.$page['title'];
    @endphp

    <div class="container news-detail-container profile-detail-container">
        <div id="sc_innerpage_wrap" class="news-detail-layout profile-article-layout" data-hide-back-to-top data-disable-scroll-reveal>
            <article class="sc_innerpage_contentbx single-article village-profile-article">
                <header class="entry-header">
                    <h1 class="entry-title">{{ $page['title'] }}</h1>
                    <div class="postmeta" aria-label="Informasi artikel">
                        <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>Diperbarui {{ $detailUpdatedAt->translatedFormat('d F Y') }}</span>
                        <span class="post-author"><i class="far fa-user" aria-hidden="true"></i>Pemerintah Desa Sukomulyo</span>
                        <button class="profile-print-button" type="button" data-print-article>
                            <i class="fas fa-print" aria-hidden="true"></i>Cetak Artikel
                        </button>
                    </div>
                </header>

                <figure class="news-detail-hero profile-detail-hero">
                    <img src="{{ $detailHero }}" alt="{{ $detailHeroAlt }}">
                    <figcaption>{{ $page['description'] }}</figcaption>
                </figure>

                <div class="article-reading-body">
                    <div class="entry-content profile-detail-content profile-detail-content--{{ $commentContext['page_key'] }}">
                        @forelse ($sections as $section)
                            <section
                                class="profile-article-section profile-article-section--{{ $section->section_key }}"
                                data-profile-section="{{ $section->section_key }}"
                                @if($section->section_key === 'history') data-disable-table-copy @endif
                            >
                                @if($section->section_key !== 'history')
                                    <h2>{{ $section->title }}</h2>
                                @endif
                                @if ($section->image)
                                    <img class="profile-section-image" src="{{ $section->image->url }}" alt="{{ $section->image->alt_text ?: $section->title }}">
                                @endif
                                <div class="profile-cms-content">{!! $section->content !!}</div>
                            </section>
                        @empty
                            @foreach ($page['fallback'] as $section)
                                <section class="profile-article-section profile-article-section--fallback">
                                    @if($commentContext['page_key'] !== 'sejarah')
                                        <h2>{{ $section['title'] }}</h2>
                                    @endif
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
