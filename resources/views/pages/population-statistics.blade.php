@php
    $updatedLabel = $genderSummary['updated_at']
        ? \Carbon\Carbon::parse($genderSummary['updated_at'])->translatedFormat('d F Y')
        : 'Belum tersedia';
    $defaultHistory = collect($populationTrend)
        ->filter(fn (array $row) => $row['year'] >= $defaultRange['from'] && $row['year'] <= $defaultRange['to'])
        ->sortBy('year')
        ->values();
    $previousTotal = null;
    $defaultHistory = $defaultHistory->map(function (array $row) use (&$previousTotal) {
        $change = $previousTotal === null ? null : $row['total'] - $previousTotal;
        $growth = $previousTotal === null || $previousTotal === 0
            ? null
            : round($change / $previousTotal * 100, 2);
        $previousTotal = $row['total'];

        return array_merge($row, ['change' => $change, 'growth_percentage' => $growth]);
    });
    if ($tableSort === 'desc') {
        $defaultHistory = $defaultHistory->reverse()->values();
    }
@endphp

<x-layouts.app :title="$page['title']" :description="$page['description']">
    <x-page-header
        :title="$page['title']"
        :description="$page['description']"
        :show-heading="false"
        :breadcrumbs="[
            ['label' => 'Data Desa', 'url' => route('data-desa-statistik')],
            ['label' => $page['title']],
        ]"
    />

    <div class="container">
        <div id="sc_innerpage_wrap" class="population-statistics-wrap statistics-page-layout">
            <section class="sc_innerpage_contentbx population-statistics" data-population-statistics>
                <section class="population-chart-card" aria-labelledby="population-composition-title">
                    <header class="population-chart-card__heading population-statistics-heading">
                        <div class="population-statistics-heading__content">
                            <h1 id="population-composition-title" class="entry-title">Komposisi Penduduk Tahun {{ $genderSummary['year'] }}</h1>
                            <div class="postmeta" aria-label="Informasi data statistik">
                                <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>Diperbarui 28 Juli 2026</span>
                                <span class="post-author"><i class="far fa-user" aria-hidden="true"></i>Pemerintah Desa Sukomulyo</span>
                            </div>
                        </div>
                        <div class="population-download-control">
                            <button
                                class="profile-print-button population-download-button"
                                type="button"
                                aria-expanded="false"
                                aria-controls="population-actual-download-menu"
                                data-population-export-toggle="actual"
                            >
                                <i class="fas fa-download" aria-hidden="true"></i>
                                Unduh Data Aktual
                                <i class="fas fa-chevron-down population-download-button__chevron" aria-hidden="true"></i>
                            </button>
                            <div
                                id="population-actual-download-menu"
                                class="population-download-menu"
                                role="menu"
                                aria-label="Pilihan format unduhan data aktual"
                                data-population-export-menu="actual"
                                hidden
                            >
                                <strong class="population-download-menu__title">Pilih format file</strong>
                                <div class="population-download-menu__formats">
                                    @foreach (['svg' => 'SVG', 'pdf' => 'PDF', 'jpg' => 'JPG', 'png' => 'PNG'] as $format => $label)
                                        <button type="button" role="menuitem" data-population-export-action data-export-scope="actual" data-export-format="{{ $format }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </header>

                    <div class="population-composition-grid">
                        <div class="population-chart-card__canvas">
                            <div
                                class="population-pie-chart"
                                data-population-pie
                                role="img"
                                tabindex="0"
                                aria-label="Grafik komposisi penduduk tahun {{ $genderSummary['year'] }}: {{ number_format($genderSummary['male'], 0, ',', '.') }} laki-laki dan {{ number_format($genderSummary['female'], 0, ',', '.') }} perempuan"
                            ></div>
                            <p class="population-empty-state" data-population-pie-empty @if($genderSummary['total'] > 0) hidden @endif>
                                Data komposisi penduduk tahun {{ $genderSummary['year'] }} belum tersedia.
                            </p>
                        </div>

                    </div>

                    <div class="population-table-scroll population-summary-table-wrap">
                        <table class="population-summary-table">
                            <caption class="screen-reader-text">
                                Komposisi penduduk Desa Sukomulyo tahun {{ $genderSummary['year'] }} berdasarkan jenis kelamin
                            </caption>
                            <thead>
                                <tr>
                                    <th scope="col">Tahun</th>
                                    <th scope="col">Jumlah Laki-laki</th>
                                    <th scope="col">Persentase Laki-laki</th>
                                    <th scope="col">Jumlah Perempuan</th>
                                    <th scope="col">Persentase Perempuan</th>
                                    <th scope="col">Jumlah Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">{{ $genderSummary['year'] }}</th>
                                    <td>{{ number_format($genderSummary['male'], 0, ',', '.') }} jiwa</td>
                                    <td>{{ number_format($genderSummary['male_percentage'], 2, ',', '.') }}%</td>
                                    <td>{{ number_format($genderSummary['female'], 0, ',', '.') }} jiwa</td>
                                    <td>{{ number_format($genderSummary['female_percentage'], 2, ',', '.') }}%</td>
                                    <td><strong>{{ number_format($genderSummary['total'], 0, ',', '.') }} jiwa</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="population-trend-panel" class="population-trend-panel" data-population-trend-panel aria-labelledby="population-trend-title">
                    <div class="population-chart-card__heading">
                        <div>
                            <h2 id="population-trend-title" class="population-trend-title" tabindex="-1">Pertumbuhan Penduduk Tahunan</h2>
                        </div>
                        <div class="population-download-control population-download-control--annual">
                            <button
                                class="profile-print-button population-download-button"
                                type="button"
                                aria-expanded="false"
                                aria-controls="population-annual-download-menu"
                                data-population-export-toggle="annual"
                            >
                                <i class="fas fa-download" aria-hidden="true"></i>
                                Unduh Data Tahunan
                                <i class="fas fa-chevron-down population-download-button__chevron" aria-hidden="true"></i>
                            </button>
                            <div
                                id="population-annual-download-menu"
                                class="population-download-menu"
                                role="menu"
                                aria-label="Pilihan format unduhan data tahunan"
                                data-population-export-menu="annual"
                                hidden
                            >
                                <strong class="population-download-menu__title">Pilih format file</strong>
                                <div class="population-download-menu__formats">
                                    @foreach (['svg' => 'SVG', 'pdf' => 'PDF', 'jpg' => 'JPG', 'png' => 'PNG'] as $format => $label)
                                        <button type="button" role="menuitem" data-population-export-action data-export-scope="annual" data-export-format="{{ $format }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="get" action="{{ route('data-statistik.population') }}" class="population-range-filter" data-population-year-filter>
                        <div>
                            <label for="population-from-year">Dari Tahun</label>
                            <select id="population-from-year" name="from_year" data-population-from-year>
                                @foreach ($availableYears as $year)
                                    <option value="{{ $year }}" @selected($year === $defaultRange['from'])>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="population-to-year">Sampai Tahun</label>
                            <select id="population-to-year" name="to_year" data-population-to-year>
                                @foreach ($availableYears as $year)
                                    <option value="{{ $year }}" @selected($year === $defaultRange['to'])>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="population-sort">Urutan Tabel</label>
                            <select id="population-sort" name="sort" data-population-sort>
                                <option value="asc" @selected($tableSort === 'asc')>Terlama ke Terbaru</option>
                                <option value="desc" @selected($tableSort === 'desc')>Terbaru ke Terlama</option>
                            </select>
                        </div>
                        <button type="submit">Terapkan Rentang</button>
                    </form>
                    <p class="population-filter-status" data-population-filter-status aria-live="polite"></p>

                    <div class="population-chart-card population-chart-card--trend">
                        <div
                            class="population-line-chart"
                            data-population-line
                            role="img"
                            tabindex="0"
                            aria-label="Grafik pertumbuhan penduduk tahunan Desa Sukomulyo"
                        ></div>
                        <p class="population-empty-state" data-population-line-empty hidden>
                            Data pertumbuhan penduduk pada rentang ini belum tersedia.
                        </p>
                    </div>

                    <div class="population-table-scroll population-history-table-wrap" tabindex="0" aria-label="Tabel riwayat dapat digulir secara horizontal">
                        <table class="population-history-table">
                            <caption class="screen-reader-text">Riwayat jumlah penduduk Desa Sukomulyo berdasarkan tahun</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Tahun</th>
                                    <th scope="col">Laki-laki</th>
                                    <th scope="col">Perempuan</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Perubahan</th>
                                    <th scope="col">Pertumbuhan</th>
                                    <th scope="col">Sumber</th>
                                </tr>
                            </thead>
                            <tbody data-population-history-body>
                                @forelse ($defaultHistory as $row)
                                    <tr>
                                        <th scope="row">{{ $row['year'] }}</th>
                                        <td>{{ number_format($row['male'], 0, ',', '.') }} jiwa</td>
                                        <td>{{ number_format($row['female'], 0, ',', '.') }} jiwa</td>
                                        <td><strong>{{ number_format($row['total'], 0, ',', '.') }} jiwa</strong></td>
                                        <td>{{ $row['change'] === null ? '—' : ($row['change'] >= 0 ? '+' : '').number_format($row['change'], 0, ',', '.').' jiwa' }}</td>
                                        <td>{{ $row['growth_percentage'] === null ? '—' : ($row['growth_percentage'] >= 0 ? '+' : '').number_format($row['growth_percentage'], 2, ',', '.').'%' }}</td>
                                        <td>
                                            {{ $row['source'] ?: 'Sumber belum dicantumkan' }}
                                            @if ($row['reference_date'])
                                                <small>per {{ \Carbon\Carbon::parse($row['reference_date'])->format('d-m-Y') }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7">Belum ada data tahunan yang dipublikasikan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </section>

                <script type="application/json" id="population-statistics-data">{!! \Illuminate\Support\Js::encode([
                    'summary' => $genderSummary,
                    'trend' => $populationTrend,
                    'availableYears' => $availableYears,
                    'defaultRange' => $defaultRange,
                    'sort' => $tableSort,
                ]) !!}</script>
            </section>

            <x-statistics-sidebar />
        </div>
    </div>
</x-layouts.app>
