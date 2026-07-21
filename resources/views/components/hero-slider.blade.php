@props(['articles'])

<section class="hero-slider" data-slider aria-label="Informasi unggulan">
    <div class="hero-slides">
        @foreach (array_slice($articles, 0, 3) as $index => $article)
            <article class="hero-slide {{ $index === 0 ? 'is-active' : '' }}" data-slide style="background-position: {{ ['35% 35%', '50% 50%', '65% 65%'][$index] }}; background-image: linear-gradient(90deg, rgba(20, 25, 31, .88), rgba(20, 25, 31, .28)), url('{{ $article['image'] }}')" aria-hidden="{{ $index === 0 ? 'false' : 'true' }}">
                <div class="container hero-content">
                    <div class="hero-copy">
                        <h2>{{ $article['title'] }}</h2>
                        <p>{{ $article['excerpt'] }}</p>
                        <a class="slide_more" href="{{ route('berita-desa.show', $article['slug']) }}">Baca Selengkapnya</a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="pixel-transition" data-pixel-transition aria-hidden="true"></div>

    <div class="slider-controls container">
        <button type="button" data-slider-prev aria-label="Slide sebelumnya"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
        <div class="slider-dots" aria-label="Pilih slide">
            @foreach (array_slice($articles, 0, 3) as $index => $article)
                <button class="{{ $index === 0 ? 'is-active' : '' }}" type="button" data-slider-dot="{{ $index }}" aria-label="Tampilkan slide {{ $index + 1 }}"></button>
            @endforeach
        </div>
        <button type="button" data-slider-next aria-label="Slide berikutnya"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
    </div>
</section>
