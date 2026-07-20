<x-layouts.app :title="$article['title']" :description="$article['excerpt']">
    <x-page-header title="Detail Berita" />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <article class="sc_innerpage_contentbx single-article">
                <header class="entry-header">
                    <a class="article-category" href="{{ route('news.category', $article['category_slug']) }}">{{ $article['category'] }}</a>
                    <h1 class="entry-title">{{ $article['title'] }}</h1>
                    <div class="postmeta">
                        <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>{{ $article['date'] }}</span>
                        <span><i class="far fa-user" aria-hidden="true"></i>Pemerintah Desa Sukomulyo</span>
                    </div>
                </header>

                <img class="single-article-image" src="{{ $article['image'] }}" alt="{{ $article['title'] }}">

                <div class="entry-content">
                    @foreach ($article['content'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>

                <footer class="article-footer">
                    <strong>Tag:</strong>
                    @foreach ($article['tags'] as $tag)<span>{{ $tag }}</span>@endforeach
                </footer>

                <nav class="post-navigation" aria-label="Navigasi berita">
                    <a href="{{ route('news.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Semua Berita</a>
                </nav>
            </article>

            <x-sidebar :categories="$categories" :archive-years="$archiveYears" />
            <div class="clear"></div>
        </div>
    </div>
</x-layouts.app>
