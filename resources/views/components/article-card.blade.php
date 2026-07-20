@props(['article'])

<article class="article-card">
    <a class="article-image" href="{{ route('news.show', $article['slug']) }}">
        <img src="{{ $article['image'] }}" alt="{{ $article['title'] }}">
    </a>
    <div class="article-body">
        <div class="postmeta">
            <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>{{ $article['date'] }}</span>
            <a class="post-categories" href="{{ route('news.category', $article['category_slug']) }}">{{ $article['category'] }}</a>
        </div>
        <h2><a href="{{ route('news.show', $article['slug']) }}">{{ $article['title'] }}</a></h2>
        <p>{{ $article['excerpt'] }}</p>
        <a class="read-more" href="{{ route('news.show', $article['slug']) }}">Baca selengkapnya <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
    </div>
</article>
