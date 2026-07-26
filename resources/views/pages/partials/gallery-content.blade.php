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
                            data-image="{{ $photo['src'] }}"
                            data-title="{{ $photo['title'] }}"
                            data-caption="{{ $photo['caption'] }}"
                            aria-label="Lihat {{ $photo['title'] }}"
                        >
                            <img src="{{ $photo['src'] }}" alt="{{ $photo['title'] }}" loading="lazy">
                            <span class="gallery-collage-overlay">
                                <strong>{{ $photo['title'] }}</strong>
                                <span><i class="far fa-calendar-alt" aria-hidden="true"></i>{{ $photo['date'] }}</span>
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
</div>
