<x-layouts.app title="Galeri Desa">
    <x-page-header title="Galeri Desa" description="Dokumentasi kegiatan, pelayanan, dan potensi Desa Sukomulyo." />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth">
                <div class="gallery-grid">
                    @foreach ($photos as $photo)
                        <button class="gallery-item" type="button" data-gallery-item data-image="{{ $photo['src'] }}" data-title="{{ $photo['title'] }}" data-caption="{{ $photo['caption'] }}">
                            <img src="{{ $photo['src'] }}" alt="{{ $photo['title'] }}">
                            <span class="gallery-overlay"><i class="fas fa-search-plus" aria-hidden="true"></i><strong>{{ $photo['title'] }}</strong></span>
                        </button>
                    @endforeach
                </div>
            </section>
        </div>
    </div>

    <dialog class="gallery-dialog" data-gallery-dialog aria-labelledby="gallery-dialog-title">
        <button class="dialog-close" type="button" data-gallery-close aria-label="Tutup galeri"><i class="fas fa-times" aria-hidden="true"></i></button>
        <img data-gallery-image src="" alt="">
        <div class="dialog-caption">
            <h2 id="gallery-dialog-title" data-gallery-title></h2>
            <p data-gallery-caption></p>
        </div>
    </dialog>
</x-layouts.app>
