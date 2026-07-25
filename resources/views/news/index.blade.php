<x-layouts.app :title="$heading" :description="$description">
    <x-page-header
        :title="$heading"
        :description="$heading === 'Berita Desa' ? null : $description"
        :show-breadcrumbs="$heading !== 'Berita Desa'"
        :show-divider="$heading !== 'Berita Desa'"
        :breadcrumbs="$heading === 'Berita Desa'
        ? [['label' => 'Berita']]
        : [['label' => 'Berita', 'url' => route('berita-desa.index')], ['label' => $heading]]"
    />

    @if (($showFeatured ?? false) && count($featuredArticles ?? []) > 0)
        <section class="featured-news-section" aria-label="Berita utama">
            <div class="container">
                <div class="featured-news-grid">
                    @foreach ($featuredArticles as $article)
                        <article class="featured-news-card {{ $loop->first ? 'featured-news-card--main' : '' }}">
                            <a class="featured-news-link" href="{{ route('berita-desa.show', $article['slug']) }}">
                                <img src="{{ $article['image'] }}" alt="{{ $article['title'] }}">
                                <span class="featured-news-overlay" aria-hidden="true"></span>
                                <span class="featured-news-content">
                                    <strong>{{ $article['title'] }}</strong>
                                    <span class="featured-news-meta">
                                        <span class="featured-news-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>{{ $article['date'] }}</span>
                                        <span aria-hidden="true">•</span>
                                        <span class="featured-news-category">{{ $article['category'] }}</span>
                                    </span>
                                    @if ($loop->first)<span class="featured-news-excerpt">{{ $article['excerpt'] }}</span>@endif
                                </span>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <div class="container">
        <div id="sc_innerpage_wrap" class="news-page-layout">
            <section class="sc_innerpage_contentbx">
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
                :selected-category="$selectedCategory ?? ''"
            />
            <div class="clear"></div>
        </div>
    </div>
</x-layouts.app>
