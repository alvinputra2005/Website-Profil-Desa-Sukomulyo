<x-layouts.app title="Data Desa">
    <x-page-header title="Data Desa" description="Ringkasan data kependudukan, wilayah, dan mata pencaharian masyarakat Desa Sukomulyo." />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth">
                <div id="statistik-penduduk" class="statistics-grid">
                    @foreach ($statistics as $statistic)
                        <article class="statistic-card">
                            <i class="{{ $statistic['icon'] }}" aria-hidden="true"></i>
                            <div><strong>{{ $statistic['value'] }}</strong><span>{{ $statistic['unit'] }}</span></div>
                            <p>{{ $statistic['label'] }}</p>
                        </article>
                    @endforeach
                </div>

                <div id="visualisasi-data" class="data-section-grid">
                    <section id="jenis-kelamin" class="data-panel">
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

                    <section id="statistik-ekonomi" class="data-panel">
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

                <div class="data-section-grid">
                    @foreach ($distributions as $anchor => $distribution)
                        <section id="{{ $anchor }}" class="data-panel">
                            <span class="section-kicker">Kependudukan</span>
                            <h2>{{ $distribution['title'] }}</h2>
                            @forelse ($distribution['items'] as $item)
                                <div class="data-progress">
                                    <div>
                                        <span>{{ $item['label'] }}</span>
                                        <strong>{{ number_format($item['total'], 0, ',', '.') }} jiwa</strong>
                                    </div>
                                    <div class="progress-track" role="progressbar" aria-label="{{ $item['label'] }} {{ $item['percentage'] }} persen" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $item['percentage'] }}">
                                        <span class="progress-fill" style="width: {{ $item['percentage'] }}%">
                                            <span class="progress-percent">{{ $item['percentage'] }}%</span>
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p>Data belum tersedia.</p>
                            @endforelse
                        </section>
                    @endforeach
                </div>

                <section id="idm" class="data-panel">
                    <span class="section-kicker">Indeks Desa Membangun</span>
                    <h2>IDM Desa Sukomulyo</h2>
                    @if ($idm)
                        <div class="statistics-grid">
                            @foreach ([['IDM', $idm['idm_score']], ['IKS', $idm['iks_score']], ['IKE', $idm['ike_score']], ['IKL', $idm['ikl_score']]] as [$label, $value])
                                <article class="statistic-card">
                                    <i class="fas fa-chart-line" aria-hidden="true"></i>
                                    <div><strong>{{ number_format((float) $value, 4, ',', '.') }}</strong></div>
                                    <p>{{ $label }}</p>
                                </article>
                            @endforeach
                        </div>
                        <p>Status tahun {{ $idm['year'] }}: <strong>{{ $idm['status_label'] }}</strong>@if($idm['source']) · Sumber: {{ $idm['source'] }}@endif</p>
                    @else
                        <p>Data IDM belum tersedia.</p>
                    @endif
                </section>

                @if ($importedStatisticCategories->isNotEmpty())
                    <section class="data-panel" aria-labelledby="imported-statistics-title">
                        <span class="section-kicker">Data Sensus</span>
                        <h2 id="imported-statistics-title">Dataset Statistik Terpublikasi</h2>
                        <div class="statistics-grid">
                            @foreach ($importedStatisticCategories as $category)
                                <article class="statistic-card">
                                    <i class="fas {{ $category->icon ?: 'fa-table' }}" aria-hidden="true"></i>
                                    <div><strong>{{ $category->datasets->count() }}</strong><span>dataset</span></div>
                                    <p>
                                        <a href="{{ route('data-statistik.detail', ['section' => $category->slug]) }}">
                                            {{ $category->name }}
                                        </a>
                                    </p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <p class="data-note"><i class="fas fa-info-circle" aria-hidden="true"></i> Data pada halaman ini merupakan struktur awal dan dapat diperbarui sesuai data resmi desa terbaru.</p>
            </section>
        </div>
    </div>
</x-layouts.app>
