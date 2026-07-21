<x-layouts.app :title="$heading" :description="$description">
    <x-page-header :title="$heading" :description="$description" />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx">
                @forelse ($visibleArticles as $article)
                    <x-article-card :article="$article" />
                @empty
                    <div class="empty-state">
                        <i class="far fa-folder-open" aria-hidden="true"></i>
                        <h2>Belum Ada Berita</h2>
                        <p>Belum ada berita yang tersimpan pada arsip ini.</p>
                        <a class="button" href="{{ route('berita-desa.index') }}">Kembali ke Berita</a>
                    </div>
                @endforelse
            </section>

            <x-sidebar :categories="$categories" :archive-years="$archiveYears" />
            <div class="clear"></div>
        </div>
    </div>
</x-layouts.app>
