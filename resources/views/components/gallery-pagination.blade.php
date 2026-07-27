@if ($paginator->hasPages())
    <nav class="news-pagination gallery-pagination" role="navigation" aria-label="Navigasi halaman galeri">
        @if ($paginator->onFirstPage())
            <span class="news-pagination-arrow is-disabled" aria-disabled="true">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
                <span class="screen-reader-text">Halaman sebelumnya</span>
            </span>
        @else
            <a class="news-pagination-arrow" href="{{ $paginator->previousPageUrl() }}#gallery-list" rel="prev" data-ajax-scroll-target="#gallery-list">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
                <span class="screen-reader-text">Halaman sebelumnya</span>
            </a>
        @endif

        <div class="news-pagination-pages">
            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                @if ($page === $paginator->currentPage())
                    <span class="is-current" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}#gallery-list" aria-label="Buka halaman galeri {{ $page }}" data-ajax-scroll-target="#gallery-list">{{ $page }}</a>
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a class="news-pagination-arrow" href="{{ $paginator->nextPageUrl() }}#gallery-list" rel="next" data-ajax-scroll-target="#gallery-list">
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
@endif
