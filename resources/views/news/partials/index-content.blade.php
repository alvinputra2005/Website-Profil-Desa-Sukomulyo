<div id="news-ajax-root" data-ajax-scope="#news-ajax-root" data-page-title="{{ $heading }} | {{ $site['name'] }}" data-page-description="{{ $description }}">
    <x-page-header
        :title="$heading"
        :description="$description"
        :show-heading="false"
        :breadcrumbs="$heading === 'Berita Desa'
            ? [
                ['label' => 'Informasi Desa', 'url' => route('informasi-publik-desa')],
                ['label' => 'Berita Desa'],
            ]
            : [
                ['label' => 'Informasi Desa', 'url' => route('informasi-publik-desa')],
                ['label' => 'Berita Desa', 'url' => route('berita-desa.index')],
                ['label' => $heading],
            ]"
    />

    @if (($showFeatured ?? false) && count($featuredArticles ?? []) > 0)
        <section class="featured-news-section" aria-label="Berita utama">
            <div class="container news-content-container">
                <div class="featured-news-grid">
                    @foreach ($featuredArticles as $article)
                        <article class="featured-news-card {{ $loop->first ? 'featured-news-card--main' : '' }}">
                            <a class="featured-news-link" href="{{ route('berita-desa.show', $article['slug']) }}">
                                <img src="{{ $article['medium_image'] ?? $article['image'] }}" alt="{{ $article['title'] }}">
                                <span class="featured-news-overlay" aria-hidden="true"></span>
                                <span class="featured-news-content">
                                    <strong>{{ $article['title'] }}</strong>
                                    <span class="featured-news-meta">
                                        <span class="featured-news-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>{{ $article['date'] }}</span>
                                        <span aria-hidden="true">•</span>
                                        <span class="featured-news-category">{{ $article['category'] }}</span>
                                        <span class="featured-news-views"><i class="far fa-eye" aria-hidden="true"></i>{{ number_format($article['view_count'] ?? 0, 0, ',', '.') }}</span>
                                    </span>
                                    <span class="featured-news-excerpt">{{ $article['excerpt'] }}</span>
                                </span>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <div class="container news-content-container">
        <div id="sc_innerpage_wrap" class="news-page-layout" data-hide-back-to-top>
            <section id="news-list" class="sc_innerpage_contentbx" aria-labelledby="latest-news-heading">
                <header class="home-section-heading">
                    <div>
                        <h2 class="section-title" id="latest-news-heading">{{ $heading === 'Berita Desa' ? 'Berita Terkini' : $heading }}</h2>
                    </div>
                </header>

                @forelse ($visibleArticles as $article)
                    <x-article-card :article="$article" />
                @empty
                    <div class="empty-state">
                        <i class="far fa-folder-open" aria-hidden="true"></i>
                        <h2>Belum Ada Berita</h2>
                        <p>Belum ada berita yang terbit pada kategori atau tanggal yang dipilih.</p>
                        <a class="button" href="{{ route('berita-desa.index') }}">Kembali ke Berita</a>
                    </div>
                @endforelse

                @if ($visibleArticles->hasPages())
                    {{ $visibleArticles->links('components.news-pagination') }}
                @endif
            </section>

            <x-sidebar
                :categories="$categories"
                :archive-years="$archiveYears"
                :popular-articles="$popularArticles ?? []"
                :selected-category="$selectedCategory ?? ''"
                :selected-year="$selectedYear ?? ''"
            />
            <div class="clear"></div>
        </div>
    </div>
</div>
