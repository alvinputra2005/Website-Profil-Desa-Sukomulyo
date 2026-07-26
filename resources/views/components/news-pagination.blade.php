<nav class="news-pagination" role="navigation" aria-label="Navigasi halaman berita">
    @if ($paginator->onFirstPage())
        <span class="news-pagination-arrow is-disabled" aria-disabled="true">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
            <span class="screen-reader-text">Halaman sebelumnya</span>
        </span>
    @else
        <a class="news-pagination-arrow" href="{{ $paginator->previousPageUrl() }}#news-list" rel="prev" data-ajax-scroll-target="#news-list">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
            <span class="screen-reader-text">Halaman sebelumnya</span>
        </a>
    @endif

    <div class="news-pagination-pages">
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="news-pagination-ellipsis" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <span class="is-current" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}#news-list" aria-label="Buka halaman {{ $page }}" data-ajax-scroll-target="#news-list">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>

    @if ($paginator->hasMorePages())
        <a class="news-pagination-arrow" href="{{ $paginator->nextPageUrl() }}#news-list" rel="next" data-ajax-scroll-target="#news-list">
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span class="screen-reader-text">Halaman berikutnya</span>
        </a>
    @else
        <span class="news-pagination-arrow is-disabled" aria-disabled="true">
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span class="screen-reader-text">Halaman berikutnya</span>
        </span>
    @endif
</nav>
