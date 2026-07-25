<x-layouts.app :title="$page['title']">
    <x-page-header :title="$page['title']" :description="$page['description']" />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth">
                @if (!empty($page['summary']))
                    <div class="statistics-grid">
                        @foreach ([
                            ['Jumlah Penduduk', $summary['residents'], 'jiwa', 'fas fa-users'],
                            ['Kepala Keluarga', $summary['families'], 'KK', 'fas fa-home'],
                            ['Rumah Tangga', $summary['households'], 'rumah tangga', 'fas fa-building'],
                            ['Wilayah Dusun', $summary['areas'], 'dusun', 'fas fa-map-signs'],
                        ] as [$label, $value, $unit, $icon])
                            <article class="statistic-card">
                                <i class="{{ $icon }}" aria-hidden="true"></i>
                                <div><strong>{{ number_format($value, 0, ',', '.') }}</strong><span>{{ $unit }}</span></div>
                                <p>{{ $label }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif

                @if (empty($page['categories']))
                    <section class="data-panel">
                        <span class="section-kicker">Indeks Desa Membangun</span>
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
                @else
                    <div class="data-section-grid">
                        @foreach ($panels as $panel)
                            <section class="data-panel">
                                <span class="section-kicker">Kependudukan</span>
                                <h2>{{ $panel['title'] }}</h2>
                                @forelse ($panel['items'] as $item)
                                    <div class="data-progress">
                                        <div><span>{{ $item['label'] }}</span><strong>{{ number_format($item['total'], 0, ',', '.') }} jiwa</strong></div>
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
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
