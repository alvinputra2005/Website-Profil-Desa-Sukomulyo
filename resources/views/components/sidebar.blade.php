@props(['categories', 'archiveYears'])

<aside id="sidebar" aria-label="Informasi berita">
    <form class="search-form" action="{{ route('search') }}" method="GET" role="search">
        <label class="screen-reader-text" for="sidebar-search">Cari berita</label>
        <input id="sidebar-search" class="search-field" type="search" name="q" value="{{ request('q') }}" placeholder="Cari berita...">
        <button class="search-submit" type="submit" aria-label="Cari"><i class="fas fa-search" aria-hidden="true"></i></button>
    </form>

    <section class="widget">
        <h3 class="widget-title">Kategori</h3>
        <ul>
            @foreach ($categories as $category)
                <li><a href="{{ route('news.category', $category['category_slug']) }}">{{ $category['category'] }}</a></li>
            @endforeach
        </ul>
    </section>

    <section class="widget">
        <h3 class="widget-title">Arsip</h3>
        <ul>
            @foreach ($archiveYears as $year)
                <li><a href="{{ route('news.archive', $year) }}">Tahun {{ $year }}</a></li>
            @endforeach
        </ul>
    </section>
</aside>
