<x-layouts.app :title="$article['title']" :description="$article['excerpt']">
    <x-page-header title="Detail Berita" :breadcrumbs="[
        ['label' => 'Berita', 'url' => route('berita-desa.index')],
        ['label' => $article['title']],
    ]" />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <article class="sc_innerpage_contentbx single-article">
                <header class="entry-header">
                    <a class="article-category" href="{{ route('berita-desa.category', $article['category_slug']) }}">{{ $article['category'] }}</a>
                    <h1 class="entry-title">{{ $article['title'] }}</h1>
                    <div class="postmeta">
                        <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>{{ $article['date'] }}</span>
                        <span><i class="far fa-user" aria-hidden="true"></i>Pemerintah Desa Sukomulyo</span>
                    </div>
                </header>

                <div class="entry-content">
                    @if(!empty($article['html_content']))
                        {!! $article['html_content'] !!}
                    @else
                        @foreach($article['content'] ?? [] as $paragraph)<p>{{ $paragraph }}</p>@endforeach
                    @endif
                </div>

                <footer class="article-footer">
                    <strong>Tag:</strong>
                    @foreach ($article['tags'] as $tag)<span>{{ $tag }}</span>@endforeach
                </footer>

                <nav class="post-navigation" aria-label="Navigasi berita">
                    <a href="{{ route('berita-desa.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Semua Berita</a>
                </nav>
            </article>

            <x-sidebar :categories="$categories" :archive-years="$archiveYears" />
            <div class="clear"></div>
        </div>
    </div>
</x-layouts.app>
