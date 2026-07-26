@props([
    'categories',
    'archiveYears' => [],
    'popularArticles' => [],
    'selectedCategory' => request()->route('category', request('category', '')),
    'selectedYear' => request()->route('year', request('year', '')),
])

@php
    $visibleCategories = collect($categories)->take(4);
    $activeCategory = collect($categories)->firstWhere('category_slug', $selectedCategory);
    $categoryExpanded = $selectedCategory !== '';
    $archiveExpanded = $selectedYear !== '';

    if ($activeCategory && ! $visibleCategories->contains('category_slug', $selectedCategory)) {
        $visibleCategories = $visibleCategories->take(3)->push($activeCategory);
    }
@endphp

<aside id="sidebar" aria-label="Informasi berita">
    <div class="news-filter-panel">
        <form class="search-form" action="{{ route('berita-desa.search') }}" method="GET" role="search">
            <label class="screen-reader-text" for="sidebar-search">Cari berita</label>
            <input id="sidebar-search" class="search-field" type="search" name="q" value="{{ request('q') }}" placeholder="Cari berita...">
            <button class="search-submit" type="submit" aria-label="Cari"><i class="fas fa-search" aria-hidden="true"></i></button>
        </form>

        <section class="widget news-category-widget">
            <h3 class="widget-title">
                <button
                    class="widget-toggle"
                    type="button"
                    aria-expanded="{{ $categoryExpanded ? 'true' : 'false' }}"
                    aria-controls="news-category-list"
                    data-sidebar-toggle
                >
                    <span>Kategori</span>
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </button>
            </h3>
            <div id="news-category-list" class="widget-panel" data-sidebar-panel @if (! $categoryExpanded) hidden @endif>
                <ul>
                    <li>
                        <a
                            class="{{ $selectedCategory === '' ? 'is-active' : '' }}"
                            href="{{ $selectedYear !== '' ? route('berita-desa.archive', ['year' => $selectedYear]) : route('berita-desa.index') }}"
                        >
                            <span>Semua Kategori</span>
                        </a>
                    </li>
                    @foreach ($visibleCategories as $category)
                        <li>
                            <a
                                class="{{ $selectedCategory === $category['category_slug'] ? 'is-active' : '' }}"
                                href="{{ route('berita-desa.category', array_filter(['category' => $category['category_slug'], 'year' => $selectedYear])) }}"
                            >
                                <span>{{ $category['category'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section class="widget news-archive-widget">
            <h3 class="widget-title">
                <button
                    class="widget-toggle"
                    type="button"
                    aria-expanded="{{ $archiveExpanded ? 'true' : 'false' }}"
                    aria-controls="news-archive-list"
                    data-sidebar-toggle
                >
                    <span>Arsip</span>
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </button>
            </h3>
            <div id="news-archive-list" class="widget-panel" data-sidebar-panel @if (! $archiveExpanded) hidden @endif>
                <ul>
                    @foreach ($archiveYears as $year)
                        <li>
                            <a
                                class="{{ (string) $selectedYear === (string) $year ? 'is-active' : '' }}"
                                href="{{ route('berita-desa.archive', array_filter(['year' => $year, 'category' => $selectedCategory])) }}"
                            >
                                <span>Tahun {{ $year }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section class="widget news-popular-widget">
            <h3 class="widget-title"><span>Berita Populer</span></h3>
            <ol class="news-popular-list">
                @forelse ($popularArticles as $article)
                    <li>
                        <a href="{{ route('berita-desa.show', $article['slug']) }}">
                            <span class="news-popular-image">
                                <img src="{{ $article['image'] }}" alt="" loading="lazy">
                            </span>
                            <span class="news-popular-content">
                                <strong>{{ $article['title'] }}</strong>
                                <small><i class="far fa-eye" aria-hidden="true"></i>{{ number_format($article['view_count'] ?? 0, 0, ',', '.') }} kali dibaca</small>
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="news-popular-empty">Belum ada berita populer.</li>
                @endforelse
            </ol>
        </section>
    </div>
</aside>
