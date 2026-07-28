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
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth population-statistics" data-population-statistics>
                <div class="population-statistics__header" aria-label="Informasi data statistik">
                    <span class="population-data-badge"><i class="far fa-calendar-alt" aria-hidden="true"></i> Tahun Data {{ $genderSummary['year'] }}</span>
                    <span class="population-data-badge"><i class="fas fa-sync-alt" aria-hidden="true"></i> Data Terakhir Diperbarui: {{ $updatedLabel }}</span>
                    <span class="population-data-badge"><i class="fas fa-database" aria-hidden="true"></i> {{ $genderSummary['source'] }}</span>
                </div>

                <section class="population-chart-card" aria-labelledby="population-composition-title">
                    <div class="population-chart-card__heading">
                        <div>
                            <span class="section-kicker">Komposisi Penduduk</span>
                            <h2 id="population-composition-title">Komposisi Penduduk Tahun {{ $genderSummary['year'] }}</h2>
                            <p>Perbandingan jumlah penduduk laki-laki dan perempuan.</p>
                        </div>
                    </div>

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

                        <div class="population-composition-summary">
                            <p class="population-composition-summary__label">Total penduduk terdata</p>
                            <strong>{{ number_format($genderSummary['total'], 0, ',', '.') }} <span>jiwa</span></strong>
                            <dl>
                                <div>
                                    <dt><span class="population-color-dot population-color-dot--male"></span> Laki-laki</dt>
                                    <dd>{{ number_format($genderSummary['male'], 0, ',', '.') }} jiwa · {{ number_format($genderSummary['male_percentage'], 2, ',', '.') }}%</dd>
                                </div>
                                <div>
                                    <dt><span class="population-color-dot population-color-dot--female"></span> Perempuan</dt>
                                    <dd>{{ number_format($genderSummary['female'], 0, ',', '.') }} jiwa · {{ number_format($genderSummary['female_percentage'], 2, ',', '.') }}%</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="population-table-scroll" tabindex="0" aria-label="Tabel komposisi dapat digulir secara horizontal">
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

                <div class="population-analytics">
                    <button class="population-primary-action" type="button" data-population-trend-toggle>
                        <i class="fas fa-chart-line" aria-hidden="true"></i>
                        Lihat Tren Penduduk
                    </button>
                    <div class="population-analytics__menu-wrap">
                        <button
                            class="population-secondary-action"
                            type="button"
                            data-population-analytics-toggle
                            aria-expanded="false"
                            aria-controls="population-analytics-menu"
                            aria-haspopup="menu"
                        >
                            <i class="fas fa-ellipsis-v" aria-hidden="true"></i>
                            Analisis Data
                        </button>
                        <div id="population-analytics-menu" class="population-analytics-menu" role="menu" data-population-analytics-menu hidden>
                            <button type="button" role="menuitem" data-population-action="five-years">Lihat pertumbuhan 5 tahun</button>
                            <button type="button" role="menuitem" data-population-action="since-2020">Lihat data sejak 2020</button>
                            <button type="button" role="menuitem" data-population-action="download-pie">Unduh grafik sebagai gambar</button>
                        </div>
                    </div>
                </div>

                <section id="population-trend-panel" class="population-trend-panel" data-population-trend-panel aria-labelledby="population-trend-title">
                    <div class="population-chart-card__heading">
                        <div>
                            <span class="section-kicker">Perkembangan Tahunan</span>
                            <h2 id="population-trend-title" tabindex="-1">Pertumbuhan Penduduk Tahunan</h2>
                            <p>Perubahan jumlah penduduk berdasarkan data tahunan yang telah dipublikasikan.</p>
                        </div>
                    </div>

                    <div class="population-range-presets" aria-label="Preset rentang tahun">
                        <button type="button" data-population-preset="five-years">5 Tahun Terakhir</button>
                        <button type="button" data-population-preset="since-2020">Sejak 2020</button>
                        <button type="button" data-population-preset="all">Semua Data</button>
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

                    <div class="population-table-scroll" tabindex="0" aria-label="Tabel riwayat dapat digulir secara horizontal">
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

                    <aside class="population-source-note">
                        <i class="fas fa-info-circle" aria-hidden="true"></i>
                        <div>
                            <h3>Catatan sumber data</h3>
                            <p>Data 2020 dapat menggunakan basis Sensus Penduduk 2020. Data tahunan setelahnya merupakan rekapitulasi administrasi kependudukan desa sesuai tanggal referensi masing-masing dan bukan sensus baru.</p>
                            <p>Data komposisi tahun {{ $genderSummary['year'] }} diperbarui pada {{ $updatedLabel }}.</p>
                        </div>
                    </aside>
                </section>

                <script type="application/json" id="population-statistics-data">{!! \Illuminate\Support\Js::encode([
                    'summary' => $genderSummary,
                    'trend' => $populationTrend,
                    'availableYears' => $availableYears,
                    'defaultRange' => $defaultRange,
                    'sort' => $tableSort,
                ]) !!}</script>
            </section>
        </div>
    </div>
</x-layouts.app>
