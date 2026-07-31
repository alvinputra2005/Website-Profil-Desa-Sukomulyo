@php
    $updatedLabel = $latest['updated_at']
        ? \Carbon\Carbon::parse($latest['updated_at'])->translatedFormat('d F Y')
        : 'Belum tersedia';
    $labels = collect($history)->flatMap(fn (array $row) => collect($row['items'])->pluck('label'))->unique()->values();
    $showPercentage = $page['show_total'] && $latest['total'] > 0 && $page['unit'] !== 'data';
    $menu = (string) request('menu', '');
    $currentTitle = $page['current_title'] ?? $page['title'];
    $displayMode = request('display') === 'table' ? 'table' : 'chart';
    $displayQuery = request()->query();
    $chartUrl = url()->current().'?'.http_build_query(array_merge($displayQuery, ['display' => 'chart']));
    $tableUrl = url()->current().'?'.http_build_query(array_merge($displayQuery, ['display' => 'table']));
    $chartLabel = $page['chart'] === 'pie' ? 'Grafik Komposisi' : 'Grafik Data';
    $chartTypeLabel = $page['chart'] === 'pie' ? 'Pie Chart' : 'Bar Chart';
@endphp

<x-layouts.app :title="$page['title']" :description="$page['description']">
    <x-page-header
        :title="$page['title']"
        :description="$page['description']"
        :show-heading="false"
        :breadcrumbs="[
            ['label' => 'Data Statistik', 'url' => route('data-desa-statistik')],
            ['label' => $page['title']],
        ]"
    />

    <div class="container">
        <div id="sc_innerpage_wrap" class="population-statistics-wrap statistics-page-layout">
            <section class="sc_innerpage_contentbx population-statistics generic-statistics" data-generic-statistics>
                <section class="population-chart-card" aria-labelledby="generic-current-title">
                    <header class="population-chart-card__heading population-statistics-heading">
                        <div class="population-statistics-heading__content">
                            <h1 id="generic-current-title" class="entry-title">{{ $currentTitle }} Tahun {{ $latest['year'] }}</h1>
                            <div class="postmeta" aria-label="Informasi data statistik">
                                <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>Diperbarui {{ $updatedLabel }}</span>
                                <span class="post-author"><i class="far fa-user" aria-hidden="true"></i>Pemerintah Desa Sukomulyo</span>
                            </div>
                        </div>

                        <div class="population-download-control" @if($displayMode === 'table') hidden @endif>
                            <button
                                class="profile-print-button population-download-button"
                                type="button"
                                aria-expanded="false"
                                aria-controls="generic-actual-download-menu"
                                data-generic-export-toggle="actual"
                            >
                                <i class="fas fa-download" aria-hidden="true"></i>
                                Unduh Data Aktual
                                <i class="fas fa-chevron-down population-download-button__chevron" aria-hidden="true"></i>
                            </button>
                            <div
                                id="generic-actual-download-menu"
                                class="population-download-menu"
                                role="menu"
                                aria-label="Pilihan format unduhan data aktual"
                                data-generic-export-menu="actual"
                                hidden
                            >
                                <strong class="population-download-menu__title">Pilih format file</strong>
                                <div class="population-download-menu__formats">
                                    @foreach (['svg' => 'SVG', 'pdf' => 'PDF', 'jpg' => 'JPG', 'png' => 'PNG'] as $format => $label)
                                        <button type="button" role="menuitem" data-generic-export-action data-export-scope="actual" data-export-format="{{ $format }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </header>

                    <p class="population-statistics-heading__description">{{ $page['description'] }}</p>

                    <div class="statistics-display-selector" aria-label="Pilih visualisasi atau tampilan data">
                        <span class="statistics-display-selector__label">Pilih visualisasi / tampilan data</span>
                        <div class="statistics-display-selector__options">
                            <a
                                class="statistics-display-option {{ $displayMode === 'chart' ? 'is-active' : '' }}"
                                href="{{ $chartUrl }}"
                                @if($displayMode === 'chart') aria-current="page" @endif
                            >
                                <i class="fas fa-chart-line" aria-hidden="true"></i>
                                <span><strong>{{ $chartLabel }}</strong><small>{{ $chartTypeLabel }}</small></span>
                            </a>
                            <a
                                class="statistics-display-option {{ $displayMode === 'table' ? 'is-active' : '' }}"
                                href="{{ $tableUrl }}"
                                @if($displayMode === 'table') aria-current="page" @endif
                            >
                                <i class="fas fa-table" aria-hidden="true"></i>
                                <span><strong>Tabel Data</strong><small>Lihat data dalam tabel</small></span>
                            </a>
                        </div>
                    </div>

                    <div class="population-composition-grid statistics-view-panel" @if($displayMode !== 'chart') hidden @endif>
                        <div class="population-chart-card__canvas">
                            <div
                                class="population-pie-chart"
                                data-generic-current-chart
                                role="img"
                                tabindex="0"
                                aria-label="Grafik {{ $currentTitle }} tahun {{ $latest['year'] }}"
                            ></div>
                            <p class="population-empty-state" data-generic-current-empty @if(count($latest['items'])) hidden @endif>
                                Data {{ strtolower($page['title']) }} belum tersedia.
                            </p>
                        </div>
                    </div>

                    @if(isset($latestDataset))
                    <div class="population-table-scroll population-summary-table-wrap statistics-view-panel" @if($displayMode !== 'table') hidden @endif>
                        <table class="population-summary-table population-complete-table" data-generic-current-table>
                            <caption class="screen-reader-text">{{ $latestDataset->title }}</caption>
                            <x-statistic-table-header :columns="$latestDataset->columns_json ?? []" />
                            <tbody>
                                @forelse($latestDataset->rows as $row)
                                    <tr>
                                        @foreach($latestDataset->columns_json ?? [] as $column)
                                            @php
                                                $key = $column['key'];
                                                $value = match ($key) {
                                                    'area_code' => $row->area_code,
                                                    'area_name' => $row->area_name,
                                                    default => $row->values_json[$key] ?? null,
                                                };
                                            @endphp
                                            <td class="{{ $key === 'area_name' ? 'statistics-area-name' : 'statistics-data-cell' }}">
                                                @if($value === null || $value === '')
                                                    &mdash;
                                                @elseif(($column['type'] ?? null) === 'percentage' && is_numeric($value))
                                                    {{ number_format((float) $value, 2, ',', '.') }}%
                                                @elseif(($column['type'] ?? null) === 'integer' && is_numeric($value))
                                                    {{ number_format((float) $value, 0, ',', '.') }}
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ count($latestDataset->columns_json ?? []) }}">Data belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="population-table-scroll population-summary-table-wrap statistics-view-panel" @if($displayMode !== 'table') hidden @endif>
                        <table class="population-summary-table" data-generic-current-table>
                            <caption class="screen-reader-text">{{ $currentTitle }} tahun {{ $latest['year'] }}</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Kategori</th>
                                    <th scope="col">{{ $page['decimals'] > 0 ? 'Nilai' : 'Jumlah' }}</th>
                                    <th scope="col">Satuan</th>
                                    @if($showPercentage)<th scope="col">Proporsi</th>@endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latest['items'] as $item)
                                    <tr>
                                        <th scope="row">{{ $item['label'] }}</th>
                                        <td>{{ number_format($item['value'], $page['decimals'], ',', '.') }}</td>
                                        <td>{{ $item['unit'] }}</td>
                                        @if($showPercentage)
                                            <td>{{ number_format($item['value'] / $latest['total'] * 100, 2, ',', '.') }}%</td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ $showPercentage ? 4 : 3 }}">Data belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @endif
                </section>

                <section class="population-trend-panel" data-generic-trend-panel aria-labelledby="generic-trend-title" @if($displayMode !== 'chart') hidden @endif>
                    <div class="population-chart-card__heading">
                        <div>
                            <h2 id="generic-trend-title" class="population-trend-title" tabindex="-1">Perkembangan {{ $page['trend_title'] }} Tahunan</h2>
                        </div>
                        <div class="population-download-control population-download-control--annual">
                            <button
                                class="profile-print-button population-download-button"
                                type="button"
                                aria-expanded="false"
                                aria-controls="generic-annual-download-menu"
                                data-generic-export-toggle="annual"
                            >
                                <i class="fas fa-download" aria-hidden="true"></i>
                                Unduh Data Tahunan
                                <i class="fas fa-chevron-down population-download-button__chevron" aria-hidden="true"></i>
                            </button>
                            <div
                                id="generic-annual-download-menu"
                                class="population-download-menu"
                                role="menu"
                                aria-label="Pilihan format unduhan data tahunan"
                                data-generic-export-menu="annual"
                                hidden
                            >
                                <strong class="population-download-menu__title">Pilih format file</strong>
                                <div class="population-download-menu__formats">
                                    @foreach (['svg' => 'SVG', 'pdf' => 'PDF', 'jpg' => 'JPG', 'png' => 'PNG'] as $format => $label)
                                        <button type="button" role="menuitem" data-generic-export-action data-export-scope="annual" data-export-format="{{ $format }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="get" action="{{ url()->current() }}" class="population-range-filter population-range-filter--with-category" data-generic-year-filter>
                        @if($menu !== '')<input type="hidden" name="menu" value="{{ $menu }}">@endif
                        @if(!empty($selectedDataset))<input type="hidden" name="dataset" value="{{ $selectedDataset }}">@endif
                        <div>
                            <label for="generic-series">Kategori Grafik</label>
                            <select id="generic-series" data-generic-series>
                                @foreach ($labels as $label)
                                    <option value="{{ $label }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="generic-from-year">Dari Tahun</label>
                            <select id="generic-from-year" name="from_year" data-generic-from-year>
                                @foreach ($availableYears as $year)
                                    <option value="{{ $year }}" @selected($year === $defaultRange['from'])>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="generic-to-year">Sampai Tahun</label>
                            <select id="generic-to-year" name="to_year" data-generic-to-year>
                                @foreach ($availableYears as $year)
                                    <option value="{{ $year }}" @selected($year === $defaultRange['to'])>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="generic-sort">Urutan Tabel</label>
                            <select id="generic-sort" name="sort" data-generic-sort>
                                <option value="asc" @selected($tableSort === 'asc')>Terlama ke Terbaru</option>
                                <option value="desc" @selected($tableSort === 'desc')>Terbaru ke Terlama</option>
                            </select>
                        </div>
                        <button type="submit">Terapkan Rentang</button>
                    </form>
                    <p class="population-filter-status" data-generic-filter-status aria-live="polite"></p>

                    <div class="population-chart-card population-chart-card--trend">
                        <div
                            class="population-line-chart"
                            data-generic-trend-chart
                            role="img"
                            tabindex="0"
                            aria-label="Grafik perkembangan {{ $currentTitle }} tahunan"
                        ></div>
                        <p class="population-empty-state" data-generic-trend-empty @if(count($history)) hidden @endif>
                            Data tahunan belum tersedia.
                        </p>
                    </div>

                    <div class="population-table-scroll population-history-table-wrap" tabindex="0" aria-label="Tabel riwayat dapat digulir secara horizontal">
                        <table class="population-history-table" data-generic-history-table>
                            <caption class="screen-reader-text">Riwayat {{ $currentTitle }} berdasarkan tahun</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Tahun</th>
                                    @foreach($labels as $label)<th scope="col">{{ $label }}</th>@endforeach
                                    @if($page['show_total'])<th scope="col">Total</th>@endif
                                    <th scope="col">Sumber</th>
                                </tr>
                            </thead>
                            <tbody data-generic-history-body>
                                @forelse($history as $row)
                                    @php($values = collect($row['items'])->keyBy('label'))
                                    <tr>
                                        <th scope="row">{{ $row['year'] }}</th>
                                        @foreach($labels as $label)
                                            @php($item = $values->get($label))
                                            <td>{{ $item ? number_format($item['value'], $page['decimals'], ',', '.').' '.$item['unit'] : '—' }}</td>
                                        @endforeach
                                        @if($page['show_total'])<td><strong>{{ number_format($row['total'], $page['decimals'], ',', '.') }}</strong></td>@endif
                                        <td>{{ $row['source'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ $labels->count() + ($page['show_total'] ? 3 : 2) }}">Data tahunan belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <script type="application/json" id="generic-statistics-data">{!! \Illuminate\Support\Js::encode([
                    'context' => $context,
                    'page' => $page,
                    'latest' => $latest,
                    'history' => $history,
                    'allHistory' => $allHistory,
                    'availableYears' => $availableYears,
                    'defaultRange' => $defaultRange,
                    'sort' => $tableSort,
                ]) !!}</script>
            </section>

            <x-statistics-sidebar :selected-dataset="$selectedDataset ?? null" />
        </div>
    </div>
</x-layouts.app>
