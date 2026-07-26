@php
    $canonicalUrl = route('berita-desa.show', $article['slug']);
    $newsStructuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $article['title'],
        'description' => $article['seo_description'] ?? $article['excerpt'],
        'datePublished' => $article['published_at'] ?? null,
        'dateModified' => $article['updated_at'] ?? null,
        'author' => ['@type' => 'Organization', 'name' => $site['name']],
        'publisher' => ['@type' => 'GovernmentOrganization', 'name' => $site['name']],
        'image' => $article['image'] ?? null,
        'mainEntityOfPage' => $canonicalUrl,
    ];
@endphp
<x-layouts.app
    :title="$article['seo_title'] ?? $article['title']"
    :description="$article['seo_description'] ?? $article['excerpt']"
    :keywords="$article['seo_keywords'] ?? null"
    :canonical="$canonicalUrl"
    :robots="($article['status'] ?? 'published') === 'published' ? 'index, follow' : 'noindex, nofollow'"
    :og-image="$article['image'] ?? null"
    og-type="article"
    :structured-data="$newsStructuredData"
>
    <x-page-header title="Detail Berita" :breadcrumbs="[
        ['label' => 'Berita Desa', 'url' => route('berita-desa.index')],
        ['label' => $article['title']],
    ]" />

    <div class="container news-detail-container">
        <div id="sc_innerpage_wrap" class="news-detail-layout">
            <article class="sc_innerpage_contentbx single-article">
                <header class="entry-header">
                    <a class="article-category" href="{{ route('berita-desa.category', $article['category_slug']) }}">{{ $article['category'] }}</a>
                    <h1 class="entry-title">{{ $article['title'] }}</h1>
                    <div class="postmeta" aria-label="Informasi artikel">
                        <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>{{ $article['date'] }}</span>
                        <span class="post-author"><i class="far fa-user" aria-hidden="true"></i>Pemerintah Desa Sukomulyo</span>
                        <span class="post-views"><i class="far fa-eye" aria-hidden="true"></i>{{ number_format($article['view_count'] ?? 0, 0, ',', '.') }} kali dibaca</span>
                    </div>
                </header>

                @php
                    $detailImage = array_key_exists('detail_image', $article) ? $article['detail_image'] : ($article['image'] ?? null);
                    $detailImageAlt = $article['featured_image']['alt'] ?? $article['title'];
                @endphp
                @if ($detailImage)
                    <figure class="news-detail-hero">
                        <img src="{{ $detailImage }}" alt="{{ $detailImageAlt }}">
                    </figure>
                @endif

                <div class="article-reading-body">
                    @if (!empty($article['excerpt']))
                        <p class="article-lead">{{ $article['excerpt'] }}</p>
                    @endif

                    <div class="entry-content">
                        @if(!empty($article['html_content']))
                            {!! $article['html_content'] !!}
                        @else
                            @foreach($article['content'] ?? [] as $paragraph)<p>{{ $paragraph }}</p>@endforeach
                        @endif
                    </div>

                    @if (!empty($article['tags']))
                        <footer class="article-footer">
                            <strong>Tag:</strong>
                            @foreach ($article['tags'] as $tag)<span>{{ $tag }}</span>@endforeach
                        </footer>
                    @endif

                    <nav class="post-navigation" aria-label="Navigasi berita">
                        <a href="{{ route('berita-desa.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Semua Berita</a>
                    </nav>
                </div>
            </article>

            @php
                $shareUrl = route('berita-desa.show', $article['slug']);
                $shareText = $article['title'].' - '.$site['name'];
            @endphp
            <aside class="article-share-panel" aria-label="Bagikan berita">
                <button
                    class="article-share-label"
                    type="button"
                    data-share-native
                    data-share-url="{{ $shareUrl }}"
                    data-share-text="{{ $shareText }}"
                    aria-label="Bagikan berita"
                    title="Bagikan berita"
                >
                    <i class="fas fa-share-alt" aria-hidden="true"></i>
                    <span class="screen-reader-text">Bagikan berita</span>
                </button>
                <div class="article-share-actions">
                    <a
                        class="article-share-button"
                        href="https://wa.me/?text={{ rawurlencode($shareText.' '.$shareUrl) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Bagikan ke WhatsApp"
                        title="WhatsApp"
                    >
                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                        <span class="screen-reader-text">WhatsApp</span>
                    </a>
                    <a
                        class="article-share-button"
                        href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($shareUrl) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Bagikan ke Facebook"
                        title="Facebook"
                    >
                        <i class="fab fa-facebook-f" aria-hidden="true"></i>
                        <span class="screen-reader-text">Facebook</span>
                    </a>
                    <a
                        class="article-share-button"
                        href="https://www.instagram.com/"
                        target="_blank"
                        rel="noopener noreferrer"
                        data-share-instagram
                        data-share-url="{{ $shareUrl }}"
                        data-share-text="{{ $shareText }}"
                        aria-label="Salin tautan dan buka Instagram"
                        title="Instagram"
                    >
                        <i class="fab fa-instagram" aria-hidden="true"></i>
                        <span class="screen-reader-text">Salin tautan dan buka Instagram</span>
                    </a>
                </div>
                <span class="article-share-status" data-share-status aria-live="polite"></span>
            </aside>

            <x-sidebar :categories="$categories" :archive-years="$archiveYears" :popular-articles="$popularArticles ?? []" />
        </div>
    </div>
</x-layouts.app>
