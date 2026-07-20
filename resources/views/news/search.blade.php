<x-layouts.app title="Pencarian Berita">
    <x-page-header title="Pencarian Berita" description="Temukan informasi dan kegiatan Desa Sukomulyo." />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx">
                <form class="large-search-form" action="{{ route('search') }}" method="GET" role="search">
                    <label for="main-search">Kata kunci pencarian</label>
                    <div><input id="main-search" type="search" name="q" value="{{ $query }}" placeholder="Contoh: UMKM"><button type="submit"><i class="fas fa-search" aria-hidden="true"></i> Cari</button></div>
                </form>

                @if ($query !== '')
                    <h2 class="search-heading">Hasil pencarian untuk “{{ $query }}”</h2>
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

            <x-sidebar :categories="$categories" :archive-years="$archiveYears" />
            <div class="clear"></div>
        </div>
    </div>
</x-layouts.app>
