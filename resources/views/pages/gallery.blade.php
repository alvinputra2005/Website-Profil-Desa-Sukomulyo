<x-layouts.app title="Galeri Desa">
    @include('pages.partials.gallery-content')

    <dialog class="gallery-dialog" data-gallery-dialog aria-labelledby="gallery-dialog-title">
        <button class="dialog-close" type="button" data-gallery-close aria-label="Tutup galeri"><i class="fas fa-times" aria-hidden="true"></i></button>
        <img data-gallery-image src="" alt="">
        <div class="dialog-caption">
            <h2 id="gallery-dialog-title" data-gallery-title></h2>
            <p data-gallery-caption></p>
        </div>
    </dialog>
</x-layouts.app>
