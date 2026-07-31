<x-layouts.app title="Transparansi APBDes">
    <x-page-header
        title="Transparansi APBDes"
        :breadcrumbs="[
            ['label' => 'Informasi Desa', 'url' => route('informasi-publik-desa')],
            ['label' => 'Transparansi APBDes'],
        ]"
    />

    @php
        $formatMoney = static fn ($value, int $decimal = 0): string => is_numeric($value)
            ? 'Rp '.number_format((float) $value, $decimal, ',', '.')
            : 'Belum tersedia';
        $formatPercent = static fn ($value): string => is_numeric($value)
            ? number_format((float) $value, 2, ',', '.').'%'
            : 'Belum tersedia';
    @endphp

    <div class="container budget-history-container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth" data-budget-history>
                <header class="budget-history-intro">
                    <div>
                        <h1>Riwayat APBDes 2019–2025</h1>
                        <p>Perbandingan target pendapatan, anggaran belanja, dan realisasi APBDes Desa Sukomulyo dari tahun 2019 sampai 2025.</p>
                    </div>
                    <div class="population-download-control budget-download-control">
                        <button
                            class="profile-print-button population-download-button"
                            type="button"
                            aria-expanded="false"
                            aria-controls="budget-history-download-menu"
                            data-budget-export-toggle="history"
                        >
                            <i class="fas fa-download" aria-hidden="true"></i>
                            Unduh Riwayat APBDes
                            <i class="fas fa-chevron-down population-download-button__chevron" aria-hidden="true"></i>
                        </button>
                        <div
                            id="budget-history-download-menu"
                            class="population-download-menu"
                            role="menu"
                            aria-label="Pilihan format unduhan riwayat APBDes"
                            data-budget-export-menu="history"
                            hidden
                        >
                            <strong class="population-download-menu__title">Pilih format file</strong>
                            <div class="population-download-menu__formats">
                                @foreach (['svg' => 'SVG', 'pdf' => 'PDF', 'jpg' => 'JPG', 'png' => 'PNG'] as $format => $label)
                                    <button type="button" role="menuitem" data-budget-export-action data-export-scope="history" data-export-format="{{ $format }}">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </header>

                <section class="budget-chart-panel budget-history-chart-panel" aria-label="Grafik tren APBDes tahun 2019 sampai 2025">
                    <div
                        class="budget-history-chart"
                        data-budget-trend-chart
                        role="img"
                        tabindex="0"
                        aria-label="Grafik tren pendapatan, belanja, dan realisasi APBDes tahun 2019 sampai 2025"
                    ></div>
                </section>

                <div
                    class="budget-history-table-wrap"
                    tabindex="0"
                    aria-label="Tabel riwayat APBDes dapat digulir secara horizontal"
                >
                    <table class="budget-history-table">
                        <caption class="screen-reader-text">Riwayat APBDes Desa Sukomulyo tahun 2019 sampai 2025</caption>
                        <thead>
                            <tr>
                                <th scope="col">Tahun</th>
                                <th scope="col">Pendapatan</th>
                                <th scope="col">Belanja</th>
                                <th scope="col">Realisasi Belanja</th>
                                <th scope="col" data-export-align="center">Persentase Realisasi</th>
                                <th scope="col" data-budget-export-exclude>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($budgetHistory as $budget)
                                <tr>
                                    <td>
                                        <strong>{{ $budget['year'] }}</strong>
                                        @if (! empty($budget['is_partial_year']))
                                            <small class="budget-data-badge is-partial">Data sementara</small>
                                        @endif
                                    </td>
                                    <td>{{ $formatMoney($budget['income'] ?? null, 2) }}</td>
                                    <td>{{ $formatMoney($budget['spending'] ?? null, 2) }}</td>
                                    <td>{{ $formatMoney($budget['realization'] ?? null, 2) }}</td>
                                    <td><span class="budget-history-percent">{{ $formatPercent($budget['percentage'] ?? null) }}</span></td>
                                    <td data-budget-export-exclude>
                                        <a class="budget-detail-link" href="{{ route('transparansi-apbdes.show', $budget['year']) }}">
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">Data APBDes belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="screen-reader-text" data-budget-export-status aria-live="polite"></p>

                <script type="application/json" data-budget-payload>{!! \Illuminate\Support\Js::encode([
                    'mode' => 'history',
                    'history' => $budgetHistory,
                ]) !!}</script>
            </section>
        </div>
    </div>
</x-layouts.app>
