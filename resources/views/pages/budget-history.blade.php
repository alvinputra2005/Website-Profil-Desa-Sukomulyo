<x-layouts.app title="Transparansi APBDes">
    <x-page-header title="Transparansi APBDes" description="Riwayat pendapatan, belanja, dan realisasi anggaran Desa Sukomulyo selama 10 tahun terakhir." />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth">
                <header class="budget-history-intro">
                    <span class="section-kicker">Transparansi Anggaran</span>
                    <h2>Riwayat APBDes 10 Tahun Terakhir</h2>
                    <p>Perbandingan pendapatan, belanja, dan realisasi APBDes Desa Sukomulyo dari tahun 2017 sampai 2026.</p>
                </header>

                <div class="budget-history-table-wrap">
                    <table class="budget-history-table">
                        <caption class="screen-reader-text">Riwayat APBDes Desa Sukomulyo tahun 2017 sampai 2026</caption>
                        <thead>
                            <tr>
                                <th scope="col">Tahun</th>
                                <th scope="col">Pendapatan</th>
                                <th scope="col">Belanja</th>
                                <th scope="col">Realisasi</th>
                                <th scope="col">Persentase Realisasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($budgetHistory as $budget)
                                <tr>
                                    <td>{{ $budget['year'] }}</td>
                                    <td>Rp {{ number_format($budget['income'], 2, ',', '.') }}</td>
                                    <td>Rp {{ number_format($budget['spending'], 2, ',', '.') }}</td>
                                    <td>Rp {{ number_format($budget['realization'], 2, ',', '.') }}</td>
                                    <td><span class="budget-history-percent">{{ $budget['percentage'] }}%</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="data-note"><i class="fas fa-info-circle" aria-hidden="true"></i> Data pada tabel ini masih berupa data dummy dan dapat diganti dengan data resmi APBDes Desa Sukomulyo.</p>
            </section>
        </div>
    </div>
</x-layouts.app>
