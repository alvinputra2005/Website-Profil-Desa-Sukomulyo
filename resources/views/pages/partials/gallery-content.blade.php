@php
    $patternClass = $photos->currentPage() % 2 === 0
        ? 'gallery-collage--pattern-b'
        : 'gallery-collage--pattern-a';
@endphp

<div
    id="gallery-ajax-root"
    data-ajax-scope="#gallery-ajax-root"
    data-page-title="Galeri Desa | {{ $site['name'] }}"
    data-page-description="Dokumentasi kegiatan Desa Sukomulyo."
>
    <div class="container">
        <div id="sc_innerpage_wrap" class="gallery-page-wrap">
            <section class="sc_innerpage_contentbx fullwidth" aria-label="Galeri kegiatan Desa Sukomulyo">
                <div id="gallery-list" class="gallery-collage {{ $patternClass }}">
                    @foreach ($photos as $photo)
                        <button
                            class="gallery-item gallery-collage-card"
                            type="button"
                            data-gallery-item
                            data-gallery-group="{{ $photo['id'] }}"
                            data-image="{{ $photo['src'] }}"
                            data-title="{{ $photo['title'] }}"
                            data-caption="{{ $photo['caption'] }}"
                            aria-label="Lihat {{ $photo['title'] }}"
                        >
                            <img src="{{ $photo['src'] }}" alt="{{ $photo['title'] }}" loading="lazy">
                            <span class="gallery-collage-overlay">
                                <strong>{{ $photo['title'] }}</strong>
                                <span><i class="far fa-calendar-alt" aria-hidden="true"></i>{{ $photo['date'] }}</span>
                                <span><i class="far fa-images" aria-hidden="true"></i>{{ count($photo['photos']) }} foto</span>
                            </span>
                        </button>
                    @endforeach
                </div>

                @if ($photos->hasPages())
                    {{ $photos->links('components.gallery-pagination') }}
                @endif
            </section>
        </div>
    </div>

    <dialog class="gallery-dialog" data-gallery-dialog aria-labelledby="gallery-dialog-title">
        <div class="gallery-dialog-layout">
            <div class="dialog-caption">
                <h2 id="gallery-dialog-title" data-gallery-title></h2>
                <p data-gallery-caption></p>
            </div>
            <div class="gallery-dialog-media">
                <div class="gallery-dialog-main">
                    <img data-gallery-image src="" alt="">
                    <button class="dialog-close" type="button" data-gallery-close aria-label="Tutup galeri">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                    <button class="gallery-dialog-nav gallery-dialog-nav--prev" type="button" data-gallery-prev aria-label="Foto sebelumnya">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button class="gallery-dialog-nav gallery-dialog-nav--next" type="button" data-gallery-next aria-label="Foto berikutnya">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="gallery-dialog-thumbs" aria-label="Pilih foto galeri">
                    @foreach ($photos as $gallery)
                        @foreach ($gallery['photos'] as $photo)
                            <button
                                class="gallery-dialog-thumb"
                                type="button"
                                data-gallery-thumb
                                data-gallery-group="{{ $gallery['id'] }}"
                                data-image="{{ $photo['src'] }}"
                                data-title="{{ $gallery['title'] }}"
                                data-caption="{{ $gallery['caption'] }}"
                                aria-label="Tampilkan foto {{ $loop->iteration }} dari {{ $gallery['title'] }}"
                                hidden
                            >
                                <img src="{{ $photo['src'] }}" alt="" loading="lazy">
                            </button>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </dialog>
</div>
