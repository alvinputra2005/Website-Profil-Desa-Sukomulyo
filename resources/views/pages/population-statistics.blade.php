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

                <script type="application/json" id="population-statistics-data">{!! \Illuminate\Support\Js::encode([
                    'summary' => $genderSummary,
                    'trend' => [],
                    'availableYears' => [$genderSummary['year']],
                    'defaultRange' => ['from' => $genderSummary['year'], 'to' => $genderSummary['year']],
                    'sort' => 'asc',
                ]) !!}</script>
            </section>

            <x-statistics-sidebar />
        </div>
    </div>
</x-layouts.app>
