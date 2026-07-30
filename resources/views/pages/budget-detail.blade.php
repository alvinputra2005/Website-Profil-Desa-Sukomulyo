<x-layouts.app
    :title="'Detail APBDes '.$budget['summary']['year']"
    :description="'Rincian pendapatan, belanja, pembiayaan, program, dan realisasi APBDes Desa Sukomulyo tahun '.$budget['summary']['year'].'.'"
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
                        <p>Struktur anggaran, alokasi bidang, program prioritas, pembiayaan, dan perkembangan realisasi Pemerintah Desa Sukomulyo.</p>
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
                    <article class="budget-summary-card">
                        <span class="budget-summary-card__icon"><i class="fas fa-wallet" aria-hidden="true"></i></span>
                        <span>Target Pendapatan</span>
                        <strong>Rp {{ number_format($budget['summary']['income'], 0, ',', '.') }}</strong>
                    </article>
                    <article class="budget-summary-card">
                        <span class="budget-summary-card__icon"><i class="fas fa-hand-holding-usd" aria-hidden="true"></i></span>
                        <span>Pendapatan Diterima</span>
                        <strong>Rp {{ number_format($budget['summary']['income_realization'], 0, ',', '.') }}</strong>
                    </article>
                    <article class="budget-summary-card">
                        <span class="budget-summary-card__icon"><i class="fas fa-money-bill-wave" aria-hidden="true"></i></span>
                        <span>Anggaran Belanja</span>
                        <strong>Rp {{ number_format($budget['summary']['spending'], 0, ',', '.') }}</strong>
                    </article>
                    <article class="budget-summary-card">
                        <span class="budget-summary-card__icon"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
                        <span>Realisasi Belanja</span>
                        <strong>Rp {{ number_format($budget['summary']['realization'], 0, ',', '.') }}</strong>
                    </article>
                    <article class="budget-summary-card">
                        <span class="budget-summary-card__icon"><i class="fas fa-coins" aria-hidden="true"></i></span>
                        <span>Sisa Anggaran Belanja</span>
                        <strong>Rp {{ number_format($budget['summary']['remaining_budget'], 0, ',', '.') }}</strong>
                    </article>
                </section>
                <table hidden data-budget-export-section data-budget-export-title="Ringkasan APBDes">
                    <thead>
                        <tr>
                            <th scope="col">Komponen</th>
                            <th scope="col">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Target Pendapatan</td><td>Rp {{ number_format($budget['summary']['income'], 0, ',', '.') }}</td></tr>
                        <tr><td>Pendapatan Diterima</td><td>Rp {{ number_format($budget['summary']['income_realization'], 0, ',', '.') }}</td></tr>
                        <tr><td>Anggaran Belanja</td><td>Rp {{ number_format($budget['summary']['spending'], 0, ',', '.') }}</td></tr>
                        <tr><td>Realisasi Belanja</td><td>Rp {{ number_format($budget['summary']['realization'], 0, ',', '.') }}</td></tr>
                        <tr><td>Sisa Anggaran Belanja</td><td>Rp {{ number_format($budget['summary']['remaining_budget'], 0, ',', '.') }}</td></tr>
                    </tbody>
                </table>

                <section class="budget-insight-grid" aria-label="Sorotan analisis APBDes">
                    <article>
                        <span>Alokasi Terbesar</span>
                        <strong>{{ $budget['insights']['top_allocation']['name'] }}</strong>
                        <small>Rp {{ number_format($budget['insights']['top_allocation']['budget'], 0, ',', '.') }}</small>
                    </article>
                    <article>
                        <span>Penyerapan Tertinggi</span>
                        <strong>{{ $budget['insights']['best_absorption']['name'] }}</strong>
                        <small>{{ number_format($budget['insights']['best_absorption']['percentage'], 2, ',', '.') }}% terealisasi</small>
                    </article>
                </section>
                <table hidden data-budget-export-section data-budget-export-title="Sorotan APBDes">
                    <thead>
                        <tr>
                            <th scope="col">Sorotan</th>
                            <th scope="col">Bidang</th>
                            <th scope="col">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Alokasi Terbesar</td>
                            <td>{{ $budget['insights']['top_allocation']['name'] }}</td>
                            <td>Rp {{ number_format($budget['insights']['top_allocation']['budget'], 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Penyerapan Tertinggi</td>
                            <td>{{ $budget['insights']['best_absorption']['name'] }}</td>
                            <td>{{ number_format($budget['insights']['best_absorption']['percentage'], 2, ',', '.') }}%</td>
                        </tr>
                    </tbody>
                </table>

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
                        <table
                            class="budget-history-table budget-detail-table"
                            data-budget-export-table
                            data-budget-export-section
                            data-budget-export-title="Rincian Belanja per Bidang"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Kode</th>
                                    <th scope="col">Bidang Belanja</th>
                                    <th scope="col">Anggaran</th>
                                    <th scope="col">Realisasi</th>
                                    <th scope="col">Sisa</th>
                                    <th scope="col">Penyerapan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($budget['spending'] as $item)
                                    <tr>
                                        <td>{{ $item['code'] }}</td>
                                        <td class="budget-table-description">
                                            <strong>{{ $item['name'] }}</strong>
                                            <small>{{ $item['description'] }}</small>
                                        </td>
                                        <td>Rp {{ number_format($item['budget'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item['realization'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item['remaining'], 0, ',', '.') }}</td>
                                        <td>{{ number_format($item['percentage'], 2, ',', '.') }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" scope="row">Total Belanja</th>
                                    <td>Rp {{ number_format($budget['summary']['spending'], 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($budget['summary']['realization'], 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($budget['summary']['remaining_budget'], 0, ',', '.') }}</td>
                                    <td>{{ number_format($budget['summary']['percentage'], 2, ',', '.') }}%</td>
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
                        <table
                            class="budget-history-table budget-detail-table"
                            data-budget-export-section
                            data-budget-export-title="Rincian Pendapatan Desa"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Kode</th>
                                    <th scope="col">Sumber Pendapatan</th>
                                    <th scope="col">Target</th>
                                    <th scope="col">Realisasi</th>
                                    <th scope="col">Selisih</th>
                                    <th scope="col">Capaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($budget['revenue'] as $item)
                                    <tr>
                                        <td>{{ $item['code'] }}</td>
                                        <td class="budget-table-description"><strong>{{ $item['name'] }}</strong></td>
                                        <td>Rp {{ number_format($item['budget'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item['realization'], 0, ',', '.') }}</td>
                                        <td>{{ $item['variance'] >= 0 ? '+' : '−' }}Rp {{ number_format(abs($item['variance']), 0, ',', '.') }}</td>
                                        <td>{{ number_format($item['percentage'], 2, ',', '.') }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" scope="row">Total Pendapatan</th>
                                    <td>Rp {{ number_format($budget['summary']['income'], 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($budget['summary']['income_realization'], 0, ',', '.') }}</td>
                                    <td colspan="2">{{ number_format($budget['summary']['income_percentage'], 2, ',', '.') }}% dari target</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                <section class="budget-data-section" aria-labelledby="budget-program-title">
                    <header class="budget-section-heading">
                        <div>
                            <h2 id="budget-program-title">Rincian Program dan Kegiatan</h2>
                            <p>Distribusi anggaran hingga tingkat program untuk menunjukkan prioritas penggunaan APBDes.</p>
                        </div>
                    </header>
                    <div class="budget-history-table-wrap" tabindex="0" aria-label="Tabel program APBDes dapat digulir secara horizontal">
                        <table
                            class="budget-history-table budget-detail-table budget-program-table"
                            data-budget-export-section
                            data-budget-export-title="Rincian Program dan Kegiatan"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Kode</th>
                                    <th scope="col">Bidang</th>
                                    <th scope="col">Program/Kegiatan</th>
                                    <th scope="col">Anggaran</th>
                                    <th scope="col">Realisasi</th>
                                    <th scope="col">Penyerapan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($budget['programs'] as $program)
                                    <tr>
                                        <td>{{ $program['code'] }}</td>
                                        <td>{{ $program['category'] }}</td>
                                        <td class="budget-table-description"><strong>{{ $program['name'] }}</strong></td>
                                        <td>Rp {{ number_format($program['budget'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($program['realization'], 0, ',', '.') }}</td>
                                        <td>{{ number_format($program['percentage'], 2, ',', '.') }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="budget-data-section budget-quarter-section" aria-labelledby="budget-quarter-title">
                    <header class="budget-section-heading">
                        <div>
                            <h2 id="budget-quarter-title">Perkembangan Realisasi Triwulanan</h2>
                            <p>Akumulasi realisasi belanja untuk memantau kemajuan pelaksanaan sepanjang tahun.</p>
                        </div>
                    </header>
                    <div class="budget-quarter-layout">
                        <div
                            class="budget-quarter-chart"
                            data-budget-quarter-chart
                            data-budget-export-section
                            data-budget-export-chart="quarter"
                            data-budget-export-title="Grafik Perkembangan Realisasi Triwulanan"
                            role="img"
                            tabindex="0"
                            aria-label="Grafik perkembangan realisasi belanja per triwulan"
                        ></div>
                        <div class="budget-history-table-wrap" tabindex="0" aria-label="Tabel realisasi triwulanan dapat digulir secara horizontal">
                            <table
                                class="budget-history-table budget-detail-table"
                                data-budget-export-section
                                data-budget-export-title="Rincian Realisasi Triwulanan"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Periode</th>
                                        <th scope="col">Realisasi Periode</th>
                                        <th scope="col">Kumulatif</th>
                                        <th scope="col">Capaian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($budget['quarters'] as $quarter)
                                        <tr>
                                            <td>{{ $quarter['quarter'] }}</td>
                                            <td>Rp {{ number_format($quarter['period_realization'], 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($quarter['cumulative'], 0, ',', '.') }}</td>
                                            <td>{{ $quarter['percentage'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section class="budget-data-section" aria-labelledby="budget-financing-title">
                    <header class="budget-section-heading">
                        <div>
                            <h2 id="budget-financing-title">Pembiayaan Desa</h2>
                            <p>Penerimaan dan pengeluaran pembiayaan di luar komponen pendapatan dan belanja.</p>
                        </div>
                    </header>
                    <div class="budget-history-table-wrap" tabindex="0" aria-label="Tabel pembiayaan desa dapat digulir secara horizontal">
                        <table
                            class="budget-history-table budget-detail-table"
                            data-budget-export-section
                            data-budget-export-title="Pembiayaan Desa"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Kode</th>
                                    <th scope="col">Uraian</th>
                                    <th scope="col">Jenis</th>
                                    <th scope="col">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($budget['financing'] as $item)
                                    <tr>
                                        <td>{{ $item['code'] }}</td>
                                        <td class="budget-table-description"><strong>{{ $item['name'] }}</strong></td>
                                        <td>{{ $item['type'] }}</td>
                                        <td>Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" scope="row">Pembiayaan Neto</th>
                                    <td>Rp {{ number_format($budget['financing_summary']['net'], 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                <p class="screen-reader-text" data-budget-export-status aria-live="polite"></p>

                <script type="application/json" data-budget-payload>{!! \Illuminate\Support\Js::encode([
                    'mode' => 'detail',
                    'budget' => $budget,
                ]) !!}</script>
            </article>
        </div>
    </div>
</x-layouts.app>
