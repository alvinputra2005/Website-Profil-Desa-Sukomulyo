<x-layouts.app :title="$dataset->short_title ?: $dataset->title" :description="'Tabel '.$dataset->title.' periode '.$dataset->period">
    <x-page-header
        :title="$dataset->short_title ?: $dataset->title"
        :description="'Dataset '.$category->name.' periode '.($dataset->period ?: $dataset->year).'.'"
        :show-heading="false"
        :breadcrumbs="[
            ['label' => 'Data Statistik', 'url' => route('data-desa-statistik')],
            ['label' => $category->name, 'url' => route('data-statistik.detail', ['section' => $category->slug])],
            ['label' => $dataset->short_title ?: $dataset->title],
        ]"
    />

    <div class="container">
        <div id="sc_innerpage_wrap" class="population-statistics-wrap statistics-page-layout">
            <section class="sc_innerpage_contentbx population-statistics">
                <section class="population-chart-card" aria-labelledby="imported-dataset-title">
                    <header class="population-chart-card__heading population-statistics-heading">
                        <div class="population-statistics-heading__content">
                            <h1 id="imported-dataset-title" class="entry-title">{{ $dataset->title }}</h1>
                            <div class="postmeta" aria-label="Informasi dataset statistik">
                                <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>Periode {{ $dataset->period ?: $dataset->year }}</span>
                                <span class="post-author"><i class="far fa-file-alt" aria-hidden="true"></i>{{ data_get($dataset->source_metadata_json, 'primary_file', $dataset->source ?: 'Sumber tidak tersedia') }}</span>
                            </div>
                        </div>
                    </header>

                    <div class="population-table-scroll population-summary-table-wrap">
                        <table class="population-summary-table">
                            <caption class="screen-reader-text">{{ $dataset->title }}</caption>
                            <x-statistic-table-header :columns="$dataset->columns_json ?? []" />
                            <tbody>
                                @forelse ($dataset->rows as $row)
                                    <tr>
                                        @foreach ($dataset->columns_json ?? [] as $column)
                                            @php
                                                $key = $column['key'];
                                                $value = match ($key) {
                                                    'area_code' => $row->area_code,
                                                    'area_name' => $row->area_name,
                                                    default => $row->values_json[$key] ?? null,
                                                };
                                            @endphp
                                            <td class="{{ $key === 'area_name' ? 'statistics-area-name' : 'statistics-data-cell' }}">
                                                @if ($value === null || $value === '')
                                                    —
                                                @elseif (($column['type'] ?? null) === 'percentage' && is_numeric($value))
                                                    {{ number_format((float) $value, 2, ',', '.') }}%
                                                @elseif (($column['type'] ?? null) === 'integer' && is_numeric($value))
                                                    {{ number_format((float) $value, 0, ',', '.') }}
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ count($dataset->columns_json ?? []) }}">Data belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </section>

            <x-statistics-sidebar :imported-categories="$statisticCategories" />
        </div>
    </div>
</x-layouts.app>
