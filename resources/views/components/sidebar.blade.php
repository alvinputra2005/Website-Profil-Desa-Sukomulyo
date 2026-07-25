@props([
    'categories',
    'archiveYears' => [],
    'selectedCategory' => request()->route('category', request('category', '')),
    'selectedDate' => request('date', ''),
])

<aside id="sidebar" aria-label="Informasi berita">
    <div class="news-filter-panel">
        <form class="search-form" action="{{ route('berita-desa.search') }}" method="GET" role="search">
            <label class="screen-reader-text" for="sidebar-search">Cari berita</label>
            <input id="sidebar-search" class="search-field" type="search" name="q" value="{{ request('q') }}" placeholder="Cari berita...">
            <button class="search-submit" type="submit" aria-label="Cari"><i class="fas fa-search" aria-hidden="true"></i></button>
        </form>

        <section class="widget news-category-widget">
            <h3 class="widget-title">Kategori Berita</h3>
            <ul>
                <li>
                    <a class="{{ $selectedCategory === '' ? 'is-active' : '' }}" href="{{ route('berita-desa.index', array_filter(['date' => $selectedDate])) }}">
                        <span>Semua Kategori</span>
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </a>
                </li>
                @foreach ($categories as $category)
                    <li>
                        <a class="{{ $selectedCategory === $category['category_slug'] ? 'is-active' : '' }}" href="{{ route('berita-desa.category', array_filter(['category' => $category['category_slug'], 'date' => $selectedDate])) }}">
                            <span>{{ $category['category'] }}</span>
                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="widget news-date-widget">
            <h3 class="widget-title">Kalender Berita</h3>
            <form action="{{ $selectedCategory !== '' ? route('berita-desa.category', $selectedCategory) : route('berita-desa.index') }}" method="GET">
                <label for="news-date">
                    <span>Pilih tanggal terbit</span>
                    <small>Lihat berita pada tanggal, bulan, dan tahun tertentu.</small>
                </label>
                <div class="news-date-control">
                    <i class="far fa-calendar-alt" aria-hidden="true"></i>
                    <input id="news-date" type="date" name="date" value="{{ $selectedDate }}">
                </div>
                <div class="news-date-actions">
                    <button type="submit">Tampilkan</button>
                    @if ($selectedDate !== '')
                        <a href="{{ $selectedCategory !== '' ? route('berita-desa.category', $selectedCategory) : route('berita-desa.index') }}">Hapus tanggal</a>
                    @endif
                </div>
            </form>
        </section>
    </div>
</aside>
