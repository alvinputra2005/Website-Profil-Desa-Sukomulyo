<div id="news-ajax-root" data-ajax-scope="#news-ajax-root" data-page-title="Pencarian Berita | {{ $site['name'] }}" data-page-description="Temukan informasi dan kegiatan Desa Sukomulyo.">
    <x-page-header title="Pencarian Berita" description="Temukan informasi dan kegiatan Desa Sukomulyo." :breadcrumbs="[
        ['label' => 'Informasi Desa', 'url' => route('informasi-publik-desa')],
        ['label' => 'Berita Desa', 'url' => route('berita-desa.index')],
        ['label' => 'Pencarian'],
    ]" />

    <div class="container news-content-container">
        <div id="sc_innerpage_wrap" class="news-page-layout">
            <section class="sc_innerpage_contentbx" id="news-list" aria-labelledby="search-news-heading">
                <form class="large-search-form" action="{{ route('berita-desa.search') }}" method="GET" role="search" data-ajax>
                    <label for="main-search">Kata kunci pencarian</label>
                    <div><input id="main-search" type="search" name="q" value="{{ $query }}" placeholder="Contoh: UMKM"><button type="submit"><i class="fas fa-search" aria-hidden="true"></i> Cari</button></div>
                </form>

                @if ($query !== '')
                    <h2 class="search-heading" id="search-news-heading">Hasil pencarian untuk “{{ $query }}”</h2>
                    @forelse ($results as $article)
                        <x-article-card :article="$article" />
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <h2>Tidak Ada Hasil</h2>
                            <p>Coba gunakan kata kunci lain yang lebih singkat atau berbeda.</p>
                        </div>
                    @endforelse
                @else
                    <div class="empty-state compact"><p>Masukkan kata kunci untuk mulai mencari berita.</p></div>
                @endif
            </section>

            <x-sidebar :categories="$categories" :archive-years="$archiveYears" :popular-articles="$popularArticles ?? []" />
            <div class="clear"></div>
        </div>
    </div>
</div>
