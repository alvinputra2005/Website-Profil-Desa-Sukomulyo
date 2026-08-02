<x-layouts.app :title="$page['title']">
    <x-page-header :title="$page['title']" :description="$page['description']" :breadcrumbs="[
        ['label' => 'Data Statistik', 'url' => route('data-desa-statistik')],
        ['label' => $page['title']],
    ]" />

    <div class="container">
        <div id="sc_innerpage_wrap" class="statistics-page-layout">
            <section class="sc_innerpage_contentbx">
                @if (!empty($page['summary']))
                    <div class="statistics-grid">
                        @php($summaryCards = $page['summary_cards'] ?? [
                            ['Jumlah Penduduk', $summary['residents'], 'jiwa', 'fas fa-users'],
                            ['Kepala Keluarga', $summary['families'], 'KK', 'fas fa-home'],
                            ['Wilayah Dusun', $summary['areas'], 'dusun', 'fas fa-map-signs'],
                        ])
                        @foreach ($summaryCards as [$label, $value, $unit, $icon])
                            @php($value = is_string($value) ? $summary[$value] : $value)
                            <article class="statistic-card">
                                <i class="{{ $icon }}" aria-hidden="true"></i>
                                <div><strong>{{ number_format($value, 0, ',', '.') }}</strong><span>{{ $unit }}</span></div>
                                <p>{{ $label }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif

                <div @class(['data-section-grid', 'data-section-grid--single' => count($panels) === 1])>
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
            </section>

            <x-statistics-sidebar />
        </div>
    </div>
</x-layouts.app>
