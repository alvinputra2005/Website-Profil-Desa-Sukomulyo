<x-layouts.app :title="$category->name" :description="'Dataset statistik '.$category->name.' yang telah dipublikasikan.'">
    <x-page-header
        :title="$category->name"
        description="Pilih dataset statistik yang ingin ditampilkan."
        :show-heading="false"
        :breadcrumbs="[
            ['label' => 'Data Statistik', 'url' => route('data-desa-statistik')],
            ['label' => $category->name],
        ]"
    />

    <div class="container">
        <div id="sc_innerpage_wrap" class="population-statistics-wrap statistics-page-layout">
            <section class="sc_innerpage_contentbx population-statistics">
                <section class="population-chart-card" aria-labelledby="statistics-category-title">
                    <header class="population-chart-card__heading population-statistics-heading">
                        <div class="population-statistics-heading__content">
                            <h1 id="statistics-category-title" class="entry-title">{{ $category->name }}</h1>
                            <p class="population-statistics-heading__description">
                                {{ $category->datasets->count() }} dataset statistik telah dipublikasikan.
                            </p>
                        </div>
                    </header>

                    <div class="data-section-grid">
                        @foreach ($category->datasets as $dataset)
                            <article class="data-panel">
                                <span class="section-kicker">Periode {{ $dataset->period ?: $dataset->year }}</span>
                                <h2>
                                    <a href="{{ route('data-statistik.imported.show', ['category' => $category->slug, 'dataset' => $dataset->slug]) }}">
                                        {{ $dataset->short_title ?: $dataset->title }}
                                    </a>
                                </h2>
                                <p>{{ $dataset->rows_count ?? $dataset->rows()->count() }} baris data · {{ $dataset->region_type ?: 'Wilayah desa' }}</p>
                                <a class="read-more" href="{{ route('data-statistik.imported.show', ['category' => $category->slug, 'dataset' => $dataset->slug]) }}">
                                    <span>Lihat tabel</span><i class="fas fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </article>
                        @endforeach
                    </div>
                </section>
            </section>

            <x-statistics-sidebar :imported-categories="$statisticCategories" />
        </div>
    </div>
</x-layouts.app>
