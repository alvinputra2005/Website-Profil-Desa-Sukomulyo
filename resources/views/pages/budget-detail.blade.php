@php
    $formatMoney = static fn ($value, int $decimal = 0): string => is_numeric($value)
        ? 'Rp '.number_format((float) $value, $decimal, ',', '.')
        : 'Belum tersedia';
    $formatPercent = static fn ($value): string => is_numeric($value)
        ? number_format((float) $value, 2, ',', '.').'%'
        : 'Belum tersedia';
    $isPartialYear = ! empty($budget['summary']['is_partial_year']);
    $programsAvailable = ! empty($budget['programs_available']) && ! empty($budget['programs']);
    $quartersAvailable = ! empty($budget['quarters_available']) && ! empty($budget['quarters']);
@endphp

<x-layouts.app
    :title="'Detail APBDes '.$budget['summary']['year']"
    :description="'Rincian pendapatan, belanja, pembiayaan, realisasi, dan catatan data APBDes Desa Sukomulyo tahun '.$budget['summary']['year'].'.'"
    :canonical="route('transparansi-apbdes.show', $budget['summary']['year'])"
>
    <x-page-header
        :title="'Detail APBDes '.$budget['summary']['year']"
        :breadcrumbs="[
            ['label' => 'Informasi Desa', 'url' => route('informasi-publik-desa')],
            ['label' => 'Transparansi APBDes', 'url' => route('transparansi-apbdes')],
            ['label' => 'Tahun '.$budget['summary']['year']],
        ]"
    />

    <div class="container budget-history-container">
        <div id="sc_innerpage_wrap" class="budget-detail-page">
            <article class="sc_innerpage_contentbx fullwidth" data-budget-history>
                <a class="budget-back-link" href="{{ route('transparansi-apbdes') }}">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                    Kembali ke Riwayat APBDes
                </a>

                <header class="budget-detail-heading">
                    <div>
                        <h1>Detail APBDes Tahun {{ $budget['summary']['year'] }}</h1>
                        <p>Struktur pendapatan, belanja, pembiayaan, realisasi, dan catatan data APBDes Pemerintah Desa Sukomulyo.</p>
                        @if ($isPartialYear)
                            <span class="budget-data-badge is-partial">Data sementara tahun berjalan</span>
                        @endif
                    </div>
                    <div class="population-download-control budget-download-control">
                        <button
                            class="profile-print-button population-download-button"
                            type="button"
                            aria-expanded="false"
                            aria-controls="budget-detail-download-menu"
                            data-budget-export-toggle="detail"
                        >
                            <i class="fas fa-download" aria-hidden="true"></i>
                            Unduh APBDes {{ $budget['summary']['year'] }}
                            <i class="fas fa-chevron-down population-download-button__chevron" aria-hidden="true"></i>
                        </button>
                        <div
                            id="budget-detail-download-menu"
                            class="population-download-menu"
                            role="menu"
                            aria-label="Pilihan format unduhan detail APBDes"
                            data-budget-export-menu="detail"
                            hidden
                        >
                            <strong class="population-download-menu__title">Pilih format file</strong>
                            <div class="population-download-menu__formats">
                                @foreach (['svg' => 'SVG', 'pdf' => 'PDF', 'jpg' => 'JPG', 'png' => 'PNG'] as $format => $label)
                                    <button type="button" role="menuitem" data-budget-export-action data-export-scope="detail" data-export-format="{{ $format }}">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </header>

                <section class="budget-summary-grid" aria-label="Ringkasan APBDes">
                    @foreach ([
                        ['fas fa-wallet', 'Target Pendapatan', $budget['summary']['income'] ?? null],
                        ['fas fa-hand-holding-usd', 'Pendapatan Diterima', $budget['summary']['income_realization'] ?? null],
                        ['fas fa-money-bill-wave', 'Anggaran Belanja', $budget['summary']['spending'] ?? null],
                        ['fas fa-chart-line', 'Realisasi Belanja', $budget['summary']['realization'] ?? null],
                        ['fas fa-coins', 'Sisa Anggaran Belanja', $budget['summary']['remaining_budget'] ?? null],
                        ['fas fa-balance-scale', 'Surplus/Defisit Anggaran', $budget['summary']['budget_balance'] ?? null],
                        ['fas fa-piggy-bank', 'Estimasi SiLPA', $budget['summary']['estimated_silpa'] ?? null],
                    ] as [$icon, $label, $value])
                        <article class="budget-summary-card">
                            <span class="budget-summary-card__icon"><i class="{{ $icon }}" aria-hidden="true"></i></span>
                            <span>{{ $label }}</span>
                            <strong>{{ $formatMoney($value) }}</strong>
                        </article>
                    @endforeach
                </section>

                <table hidden data-budget-export-section data-budget-export-title="Ringkasan APBDes">
                    <thead><tr><th scope="col">Komponen</th><th scope="col">Nilai</th></tr></thead>
                    <tbody>
                        <tr><td>Target Pendapatan</td><td>{{ $formatMoney($budget['summary']['income'] ?? null) }}</td></tr>
                        <tr><td>Pendapatan Diterima</td><td>{{ $formatMoney($budget['summary']['income_realization'] ?? null) }}</td></tr>
                        <tr><td>Anggaran Belanja</td><td>{{ $formatMoney($budget['summary']['spending'] ?? null) }}</td></tr>
                        <tr><td>Realisasi Belanja</td><td>{{ $formatMoney($budget['summary']['realization'] ?? null) }}</td></tr>
                        <tr><td>Sisa Anggaran Belanja</td><td>{{ $formatMoney($budget['summary']['remaining_budget'] ?? null) }}</td></tr>
                        <tr><td>Surplus/Defisit Anggaran</td><td>{{ $formatMoney($budget['summary']['budget_balance'] ?? null) }}</td></tr>
                        <tr><td>Estimasi SiLPA</td><td>{{ $formatMoney($budget['summary']['estimated_silpa'] ?? null) }}</td></tr>
                    </tbody>
                </table>

                @if (! empty($budget['insights']['top_allocation']) && ! empty($budget['insights']['best_absorption']))
                    <section class="budget-insight-grid" aria-label="Sorotan analisis APBDes">
                        <article>
                            <span>Alokasi Terbesar</span>
                            <strong>{{ $budget['insights']['top_allocation']['name'] }}</strong>
                            <small>{{ $formatMoney($budget['insights']['top_allocation']['budget'] ?? null) }}</small>
                        </article>
                        <article>
                            <span>Penyerapan Tertinggi</span>
                            <strong>{{ $budget['insights']['best_absorption']['name'] }}</strong>
                            <small>{{ $formatPercent($budget['insights']['best_absorption']['percentage'] ?? null) }} terealisasi</small>
                        </article>
                    </section>
                    <table hidden data-budget-export-section data-budget-export-title="Sorotan APBDes">
                        <thead><tr><th scope="col">Sorotan</th><th scope="col">Bidang</th><th scope="col">Nilai</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>Alokasi Terbesar</td>
                                <td>{{ $budget['insights']['top_allocation']['name'] }}</td>
                                <td>{{ $formatMoney($budget['insights']['top_allocation']['budget'] ?? null) }}</td>
                            </tr>
                            <tr>
                                <td>Penyerapan Tertinggi</td>
                                <td>{{ $budget['insights']['best_absorption']['name'] }}</td>
                                <td>{{ $formatPercent($budget['insights']['best_absorption']['percentage'] ?? null) }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                <div class="budget-chart-grid">
                    <section class="budget-chart-panel" aria-labelledby="budget-allocation-title">
                        <header>
                            <h2 id="budget-allocation-title">Komposisi Alokasi Belanja</h2>
                            <p>Proporsi anggaran berdasarkan lima bidang utama APBDes.</p>
                        </header>
                        <div
                            class="budget-detail-chart"
                            data-budget-allocation-chart
                            data-budget-export-section
                            data-budget-export-chart="allocation"
                            data-budget-export-title="Komposisi Alokasi Belanja"
                            role="img"
                            tabindex="0"
                            aria-label="Diagram komposisi alokasi belanja APBDes"
                        ></div>
                    </section>
                    <section class="budget-chart-panel" aria-labelledby="budget-comparison-title">
                        <header>
                            <h2 id="budget-comparison-title">Anggaran dan Realisasi per Bidang</h2>
                            <p>Perbandingan pagu dengan realisasi untuk mengukur penyerapan anggaran.</p>
                        </header>
                        <div
                            class="budget-detail-chart"
                            data-budget-comparison-chart
                            data-budget-export-section
                            data-budget-export-chart="comparison"
                            data-budget-export-title="Anggaran dan Realisasi per Bidang"
                            role="img"
                            tabindex="0"
                            aria-label="Grafik perbandingan anggaran dan realisasi per bidang"
                        ></div>
                    </section>
                </div>

                <section class="budget-data-section" aria-labelledby="budget-spending-title">
                    <header class="budget-section-heading">
                        <div>
                            <h2 id="budget-spending-title">Rincian Belanja per Bidang</h2>
                            <p>Rekap pagu, realisasi, sisa anggaran, dan tingkat penyerapan setiap bidang.</p>
                        </div>
                    </header>
                    <div class="budget-history-table-wrap" tabindex="0" aria-label="Tabel rincian belanja dapat digulir secara horizontal">
                        <table class="budget-history-table budget-detail-table" data-budget-export-table data-budget-export-section data-budget-export-title="Rincian Belanja per Bidang">
                            <thead>
                                <tr>
                                    <th scope="col">Kode</th><th scope="col">Bidang Belanja</th><th scope="col">Anggaran</th>
                                    <th scope="col">Realisasi</th><th scope="col">Sisa</th><th scope="col">Penyerapan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($budget['spending'] as $item)
                                    <tr>
                                        <td>{{ $item['code'] }}</td>
                                        <td class="budget-table-description">
                                            <strong>{{ $item['name'] }}</strong>
                                            <small>{{ $item['description'] }}</small>
                                            @if (! empty($item['note']))<small class="budget-data-note">{{ $item['note'] }}</small>@endif
                                        </td>
                                        <td>{{ $formatMoney($item['budget'] ?? null) }}</td>
                                        <td>{{ $formatMoney($item['realization'] ?? null) }}</td>
                                        <td>{{ $formatMoney($item['remaining'] ?? null) }}</td>
                                        <td>{{ $formatPercent($item['percentage'] ?? null) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" scope="row">Total Belanja</th>
                                    <td>{{ $formatMoney($budget['summary']['spending'] ?? null) }}</td>
                                    <td>{{ $formatMoney($budget['summary']['realization'] ?? null) }}</td>
                                    <td>{{ $formatMoney($budget['summary']['remaining_budget'] ?? null) }}</td>
                                    <td>{{ $formatPercent($budget['summary']['percentage'] ?? null) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                <section class="budget-data-section" aria-labelledby="budget-revenue-title">
                    <header class="budget-section-heading">
                        <div>
                            <h2 id="budget-revenue-title">Rincian Pendapatan Desa</h2>
                            <p>Komposisi sumber pendapatan beserta capaian terhadap target tahun anggaran.</p>
                        </div>
                    </header>
                    <div class="budget-history-table-wrap" tabindex="0" aria-label="Tabel rincian pendapatan dapat digulir secara horizontal">
                        <table class="budget-history-table budget-detail-table" data-budget-export-section data-budget-export-title="Rincian Pendapatan Desa">
                            <thead>
                                <tr>
                                    <th scope="col">Kode</th><th scope="col">Sumber Pendapatan</th><th scope="col">Target</th>
                                    <th scope="col">Realisasi</th><th scope="col">Selisih</th><th scope="col">Capaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($budget['revenue'] as $item)
                                    <tr>
                                        <td>{{ $item['code'] }}</td>
                                        <td class="budget-table-description">
                                            <strong>{{ $item['name'] }}</strong>
                                            @if (! empty($item['note']))<small class="budget-data-note">{{ $item['note'] }}</small>@endif
                                        </td>
                                        <td>{{ $formatMoney($item['budget'] ?? null) }}</td>
                                        <td>{{ $formatMoney($item['realization'] ?? null) }}</td>
                                        <td>
                                            @if (is_numeric($item['variance'] ?? null))
                                                {{ $item['variance'] >= 0 ? '+' : '−' }}{{ $formatMoney(abs((float) $item['variance'])) }}
                                            @else
                                                Belum tersedia
                                            @endif
                                        </td>
                                        <td>{{ $formatPercent($item['percentage'] ?? null) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" scope="row">Total Pendapatan</th>
                                    <td>{{ $formatMoney($budget['summary']['income'] ?? null) }}</td>
                                    <td>{{ $formatMoney($budget['summary']['income_realization'] ?? null) }}</td>
                                    <td colspan="2">{{ $formatPercent($budget['summary']['income_percentage'] ?? null) }} dari target</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                @if ($programsAvailable)
                    <section class="budget-data-section" aria-labelledby="budget-program-title">
                        <header class="budget-section-heading"><div><h2 id="budget-program-title">Rincian Program dan Kegiatan</h2><p>Distribusi anggaran hingga tingkat program.</p></div></header>
                        <div class="budget-history-table-wrap" tabindex="0" aria-label="Tabel program APBDes dapat digulir secara horizontal">
                            <table class="budget-history-table budget-detail-table budget-program-table" data-budget-export-section data-budget-export-title="Rincian Program dan Kegiatan">
                                <thead><tr><th>Kode</th><th>Bidang</th><th>Program/Kegiatan</th><th>Anggaran</th><th>Realisasi</th><th>Penyerapan</th></tr></thead>
                                <tbody>
                                    @foreach ($budget['programs'] as $program)
                                        <tr>
                                            <td>{{ $program['code'] }}</td><td>{{ $program['category'] }}</td><td>{{ $program['name'] }}</td>
                                            <td>{{ $formatMoney($program['budget'] ?? null) }}</td><td>{{ $formatMoney($program['realization'] ?? null) }}</td>
                                            <td>{{ $formatPercent($program['percentage'] ?? null) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @elseif (! empty($budget['programs_note']))
                    <section class="budget-data-notice" aria-label="Keterangan program APBDes">
                        <i class="fas fa-info-circle" aria-hidden="true"></i><p>{{ $budget['programs_note'] }}</p>
                    </section>
                @endif

                @if ($quartersAvailable)
                    <section class="budget-data-section budget-quarter-section" aria-labelledby="budget-quarter-title">
                        <header class="budget-section-heading"><div><h2 id="budget-quarter-title">Perkembangan Realisasi Triwulanan</h2><p>Akumulasi realisasi belanja sepanjang tahun.</p></div></header>
                        <div class="budget-quarter-layout">
                            <div class="budget-quarter-chart" data-budget-quarter-chart data-budget-export-section data-budget-export-chart="quarter" data-budget-export-title="Grafik Perkembangan Realisasi Triwulanan" role="img" tabindex="0" aria-label="Grafik perkembangan realisasi belanja per triwulan"></div>
                            <div class="budget-history-table-wrap" tabindex="0" aria-label="Tabel realisasi triwulanan dapat digulir secara horizontal">
                                <table class="budget-history-table budget-detail-table" data-budget-export-section data-budget-export-title="Rincian Realisasi Triwulanan">
                                    <thead><tr><th>Periode</th><th>Realisasi Periode</th><th>Kumulatif</th><th>Capaian</th></tr></thead>
                                    <tbody>
                                        @foreach ($budget['quarters'] as $quarter)
                                            <tr><td>{{ $quarter['quarter'] }}</td><td>{{ $formatMoney($quarter['period_realization'] ?? null) }}</td><td>{{ $formatMoney($quarter['cumulative'] ?? null) }}</td><td>{{ $formatPercent($quarter['percentage'] ?? null) }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                @elseif (! empty($budget['quarters_note']))
                    <section class="budget-data-notice" aria-label="Keterangan data triwulanan">
                        <i class="fas fa-calendar-alt" aria-hidden="true"></i><p>{{ $budget['quarters_note'] }}</p>
                    </section>
                @endif

                <section class="budget-data-section" aria-labelledby="budget-financing-title">
                    <header class="budget-section-heading"><div><h2 id="budget-financing-title">Pembiayaan Desa</h2><p>Penerimaan dan pengeluaran pembiayaan di luar komponen pendapatan dan belanja.</p></div></header>
                    <div class="budget-history-table-wrap" tabindex="0" aria-label="Tabel pembiayaan desa dapat digulir secara horizontal">
                        <table class="budget-history-table budget-detail-table" data-budget-export-section data-budget-export-title="Pembiayaan Desa">
                            <thead><tr><th scope="col">Kode</th><th scope="col">Uraian</th><th scope="col">Jenis</th><th scope="col">Nilai</th></tr></thead>
                            <tbody>
                                @foreach ($budget['financing'] as $item)
                                    <tr><td>{{ $item['code'] }}</td><td class="budget-table-description"><strong>{{ $item['name'] }}</strong></td><td>{{ $item['type'] }}</td><td>{{ $formatMoney($item['amount'] ?? null) }}</td></tr>
                                @endforeach
                            </tbody>
                            <tfoot><tr><th colspan="3" scope="row">Pembiayaan Neto</th><td>{{ $formatMoney($budget['financing_summary']['net'] ?? null) }}</td></tr></tfoot>
                        </table>
                    </div>
                </section>

                @if (! empty($budget['problems']) || ! empty($budget['solutions']) || ! empty($budget['data_quality']) || ! empty($budget['source_reference']['document']))
                    <section class="budget-data-section" aria-labelledby="budget-lppd-notes-title">
                        <header class="budget-section-heading">
                            <div>
                                <h2 id="budget-lppd-notes-title">Catatan Pelaksanaan dan Sumber LPPD</h2>
                                <p>Keterangan pelaksanaan anggaran serta rujukan dokumen yang menjadi dasar publikasi data.</p>
                            </div>
                        </header>
                        <div class="budget-insight-grid">
                            @if (! empty($budget['problems']))
                                <article class="budget-insight-card">
                                    <span>Permasalahan</span>
                                    <strong>{!! nl2br(e($budget['problems'])) !!}</strong>
                                </article>
                            @endif
                            @if (! empty($budget['solutions']))
                                <article class="budget-insight-card">
                                    <span>Penyelesaian/Upaya</span>
                                    <strong>{!! nl2br(e($budget['solutions'])) !!}</strong>
                                </article>
                            @endif
                        </div>
                        @if (! empty($budget['data_quality']))
                            <div class="budget-data-quality">
                                <strong>Catatan kualitas data</strong>
                                <ul>
                                    @foreach ($budget['data_quality'] as $note)<li>{{ $note }}</li>@endforeach
                                </ul>
                            </div>
                        @endif
                        @if (! empty($budget['source_reference']['document']))
                            <div class="budget-source-reference">
                                <p>
                                    <strong>Sumber:</strong> {{ $budget['source_reference']['document'] }}
                                    @if (! empty($budget['source_reference']['pages'])) — {{ $budget['source_reference']['pages'] }} @endif
                                </p>
                            </div>
                        @endif
                    </section>
                @endif

                <p class="screen-reader-text" data-budget-export-status aria-live="polite"></p>
                <script type="application/json" data-budget-payload>{!! \Illuminate\Support\Js::encode(['mode' => 'detail', 'budget' => $budget]) !!}</script>
            </article>
        </div>
    </div>
</x-layouts.app>
