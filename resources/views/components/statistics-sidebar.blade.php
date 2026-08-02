@props([
    'importedCategories' => null,
    'populationIndicators' => null,
    'selectedDataset' => null,
])
@php
    $importedCategories ??= $importedStatisticCategories ?? collect();
    $populationIndicators ??= $publicPopulationIndicators ?? collect();
    $section = (string) request()->route('section', '');
    $selectedDataset ??= (string) request('dataset', '');
    $selectedIndicator = (string) request('indicator', $populationIndicators->first()?->key ?? '');
    $display = request('display') === 'table' ? 'table' : 'chart';
@endphp

<aside id="sidebar" class="statistics-sidebar" aria-label="Navigasi data statistik" data-no-scroll-reveal>
    <nav class="statistics-sidebar__nav" data-sidebar-accordion>
        @if ($populationIndicators->isNotEmpty())
            @php
                $populationOpen = request()->routeIs('data-statistik.population');
            @endphp
            <section class="statistics-sidebar__group statistics-sidebar__group--population">
                <h2 class="statistics-sidebar__heading">
                    <button
                        class="statistics-sidebar__toggle {{ $populationOpen ? 'is-active' : '' }}"
                        type="button"
                        aria-expanded="{{ $populationOpen ? 'true' : 'false' }}"
                        aria-controls="statistics-sidebar-population"
                        data-sidebar-toggle
                        data-sidebar-accordion-toggle
                    >
                        <span class="statistics-sidebar__icon statistics-sidebar__icon--population" aria-hidden="true"><i class="fas fa-users"></i></span>
                        <span>Penduduk Terkini</span>
                        <i class="fas fa-chevron-down statistics-sidebar__chevron" aria-hidden="true"></i>
                    </button>
                </h2>
                <div id="statistics-sidebar-population" class="statistics-sidebar__panel" data-sidebar-panel @if(! $populationOpen) hidden @endif>
                    <ul class="statistics-sidebar__list">
                        @foreach($populationIndicators as $indicator)
                            <li>
                                <a
                                    class="statistics-sidebar__dataset-link {{ $populationOpen && $selectedIndicator === $indicator->key ? 'is-active' : '' }}"
                                    href="{{ route('data-statistik.population', ['indicator' => $indicator->key]) }}"
                                >
                                    <span>{{ $indicator->label }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        @foreach ($importedCategories as $category)
            @php
                $categoryOpen = $section === $category->slug;
                $datasetGroups = $category->datasets
                    ->groupBy(function ($dataset) {
                        $identity = $dataset->family && $dataset->table_number
                            ? $dataset->family.'-'.$dataset->table_number
                            : (string) ($dataset->short_title ?: $dataset->title);

                        return \Illuminate\Support\Str::slug($identity) ?: 'dataset-'.$dataset->id;
                    })
                    ->map(function ($datasets, $key) {
                        $dataset = $datasets->sortBy('year')->last();
                        $label = trim((string) ($dataset->short_title ?: $dataset->title));

                        return [
                            'key' => $key,
                            'label' => mb_strtoupper($label) === $label
                                ? \Illuminate\Support\Str::title(mb_strtolower($label))
                                : $label,
                            'years' => $datasets->pluck('year')->unique()->sort()->values()->all(),
                        ];
                    })
                    ->values();
                if ($category->slug === 'kependudukan') {
                    $datasetGroups->prepend([
                        'key' => 'jumlah-pemilih-2024',
                        'label' => 'Jumlah Pemilih 2024',
                        'years' => [2024],
                    ]);
                }
                $activeDataset = $categoryOpen
                    ? ($selectedDataset ?: (string) data_get($datasetGroups->first(), 'key', ''))
                    : '';
            @endphp
            <section class="statistics-sidebar__group">
                <h2 class="statistics-sidebar__heading">
                    <button
                        class="statistics-sidebar__toggle {{ $categoryOpen ? 'is-active' : '' }}"
                        type="button"
                        aria-expanded="{{ $categoryOpen ? 'true' : 'false' }}"
                        aria-controls="statistics-sidebar-{{ $category->slug }}"
                        data-sidebar-toggle
                        data-sidebar-accordion-toggle
                    >
                        <span class="statistics-sidebar__icon" aria-hidden="true"><i class="fas {{ $category->icon ?: 'fa-table' }}"></i></span>
                        <span>{{ $category->name }}</span>
                        <i class="fas fa-chevron-down statistics-sidebar__chevron" aria-hidden="true"></i>
                    </button>
                </h2>
                <div id="statistics-sidebar-{{ $category->slug }}" class="statistics-sidebar__panel" data-sidebar-panel @if(! $categoryOpen) hidden @endif>
                    <ul class="statistics-sidebar__list">
                        @foreach($datasetGroups as $dataset)
                            <li>
                                <a
                                    class="statistics-sidebar__dataset-link {{ $categoryOpen && $activeDataset === $dataset['key'] ? 'is-active' : '' }}"
                                    href="{{ route('data-statistik.detail', [
                                        'section' => $category->slug,
                                        'dataset' => $dataset['key'],
                                        'display' => $display,
                                    ]) }}"
                                >
                                    <span>
                                        {{ $dataset['label'] }}
                                        @if(count($dataset['years']) > 1)
                                            <small>{{ implode(', ', $dataset['years']) }}</small>
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endforeach
    </nav>
</aside>
