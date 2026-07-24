<x-layouts.app title="Data Desa/Statistik">
    <x-page-header title="Data Desa/Statistik" description="Ringkasan data kependudukan, wilayah, dan mata pencaharian masyarakat Desa Sukomulyo." />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth">
                <div class="statistics-grid">
                    @foreach ($statistics as $statistic)
                        <article class="statistic-card">
                            <i class="{{ $statistic['icon'] }}" aria-hidden="true"></i>
                            <div><strong>{{ $statistic['value'] }}</strong><span>{{ $statistic['unit'] }}</span></div>
                            <p>{{ $statistic['label'] }}</p>
                        </article>
                    @endforeach
                </div>

                <div class="data-section-grid">
                    <section class="data-panel">
                        <span class="section-kicker">Kependudukan</span>
                        <h2>Komposisi Penduduk</h2>
                        @foreach ($population as $item)
                            <div class="data-progress">
                                <div><span>{{ $item['label'] }}</span><strong>{{ number_format($item['value'], 0, ',', '.') }} jiwa</strong></div>
                                <div class="progress-track" role="progressbar" aria-label="{{ $item['label'] }} {{ $item['percentage'] }} persen" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $item['percentage'] }}">
                                    <span class="progress-fill" style="width: {{ $item['percentage'] }}%">
                                        <span class="progress-percent">{{ $item['percentage'] }}%</span>
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </section>

                    <section class="data-panel">
                        <span class="section-kicker">Ekonomi</span>
                        <h2>Mata Pencaharian</h2>
                        @foreach ($livelihoods as $item)
                            <div class="data-progress">
                                <div><span>{{ $item['label'] }}</span></div>
                                <div class="progress-track" role="progressbar" aria-label="{{ $item['label'] }} {{ $item['percentage'] }} persen" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $item['percentage'] }}">
                                    <span class="progress-fill" style="width: {{ $item['percentage'] }}%">
                                        <span class="progress-percent">{{ $item['percentage'] }}%</span>
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </section>
                </div>

                <p class="data-note"><i class="fas fa-info-circle" aria-hidden="true"></i> Data pada halaman ini merupakan struktur awal dan dapat diperbarui sesuai data resmi desa terbaru.</p>
            </section>
        </div>
    </div>
</x-layouts.app>
