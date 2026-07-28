@props(['article'])

@php
    $selectedYear = request()->route('year', request('year', ''));
@endphp

<article class="article-card">
    <a class="article-image" href="{{ route('berita-desa.show', $article['slug']) }}">
        <img src="{{ $article['thumbnail_image'] ?? $article['image'] }}" alt="{{ $article['title'] }}" loading="lazy">
    </a>
    <div class="article-body">
        <div class="postmeta">
            <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>{{ $article['date'] }}</span>
            <a class="post-categories" href="{{ route('berita-desa.category', array_filter(['category' => $article['category_slug'], 'year' => $selectedYear])) }}">{{ $article['category'] }}</a>
        </div>
        <h2><a href="{{ route('berita-desa.show', $article['slug']) }}">{{ $article['title'] }}</a></h2>
        <p>{{ $article['excerpt'] }}</p>
        <a class="read-more" href="{{ route('berita-desa.show', $article['slug']) }}">
            <span>Baca selengkapnya</span>
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>
</article>
