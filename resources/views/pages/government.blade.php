<x-layouts.app title="Struktur Pemerintahan" description="Struktur organisasi Pemerintah Desa Sukomulyo.">
    <x-page-header title="Struktur Pemerintahan" description="Struktur organisasi Pemerintah Desa Sukomulyo." :breadcrumbs="[
        ['label' => 'Profile Desa', 'url' => route('profile-desa')],
        ['label' => 'Struktur Pemerintahan'],
    ]" />

    <div class="government-page" data-hide-back-to-top data-disable-scroll-reveal>
        <div class="container government-page__container">
            <section
                class="government-organization"
                data-org-tree
                aria-labelledby="government-organization-title"
            >
                <header class="government-organization__intro">
                    <span class="section-kicker">Struktur Organisasi</span>
                    <h1 id="government-organization-title">Pemerintahan Desa Sukomulyo</h1>
                    <p>Arahkan kursor, gunakan tombol Tab, atau ketuk perangkat desa untuk melihat nama.</p>
                </header>

                <div class="org-chart-scroll" tabindex="0" aria-label="Bagan organisasi Pemerintah Desa Sukomulyo">
                    <div class="org-chart" data-org-chart>
                        <div class="org-chart__leader">
                            @if ($organization['leader'])
                                @include('pages.partials.organization-node', [
                                    'official' => $organization['leader'],
                                    'size' => 'leader',
                                    'delay' => 0,
                                ])
                            @else
                                <p class="org-chart__empty">Data Kepala Desa belum tersedia.</p>
                            @endif
                        </div>

                        <div class="org-chart__root-connector" aria-hidden="true">
                            <span class="org-line org-line--vertical" style="--org-line-delay: 90ms"></span>
                            <span class="org-line org-line--horizontal" style="--org-line-delay: 190ms"></span>
                        </div>

                        <div class="org-chart__groups">
                            <section class="org-unit org-unit--technical" aria-labelledby="org-technical-title">
                                <span class="org-line org-line--vertical org-unit__root-line" aria-hidden="true" style="--org-line-delay: 280ms"></span>
                                <h2 id="org-technical-title" class="org-unit__title">Pelaksana Teknis</h2>

                                @if (count($organization['technicalExecutors']))
                                    <div class="org-unit__connector" aria-hidden="true" style="--org-connector-inset: {{ 50 / count($organization['technicalExecutors']) }}%">
                                        <span class="org-line org-line--vertical" style="--org-line-delay: 350ms"></span>
                                        <span class="org-line org-line--horizontal" style="--org-line-delay: 420ms"></span>
                                    </div>
                                    <div class="org-unit__nodes" style="--org-columns: {{ count($organization['technicalExecutors']) }}">
                                        @foreach ($organization['technicalExecutors'] as $official)
                                            @include('pages.partials.organization-node', [
                                                'official' => $official,
                                                'delay' => 520 + ($loop->index * 80),
                                            ])
                                        @endforeach
                                    </div>
                                @else
                                    <p class="org-unit__empty">Data Kasi belum tersedia.</p>
                                @endif
                            </section>

                            <section class="org-unit org-unit--secretariat" aria-labelledby="org-secretariat-title">
                                <span class="org-line org-line--vertical org-unit__root-line" aria-hidden="true" style="--org-line-delay: 280ms"></span>
                                <h2 id="org-secretariat-title" class="org-unit__title org-unit__title--secretariat">Sekretariat Desa</h2>

                                <div class="org-unit__primary">
                                    @if ($organization['secretary'])
                                        @include('pages.partials.organization-node', [
                                            'official' => $organization['secretary'],
                                            'size' => 'emphasis',
                                            'delay' => 430,
                                        ])
                                    @else
                                        <p class="org-unit__empty">Data Sekretaris Desa belum tersedia.</p>
                                    @endif
                                </div>

                                @if (count($organization['secretariatStaff']))
                                    <div class="org-unit__connector org-unit__connector--secretariat" aria-hidden="true" style="--org-connector-inset: {{ 50 / count($organization['secretariatStaff']) }}%">
                                        <span class="org-line org-line--vertical" style="--org-line-delay: 560ms"></span>
                                        <span class="org-line org-line--horizontal" style="--org-line-delay: 630ms"></span>
                                    </div>
                                    <div class="org-unit__nodes" style="--org-columns: {{ count($organization['secretariatStaff']) }}">
                                        @foreach ($organization['secretariatStaff'] as $official)
                                            @include('pages.partials.organization-node', [
                                                'official' => $official,
                                                'delay' => 710 + ($loop->index * 80),
                                            ])
                                        @endforeach
                                    </div>
                                @else
                                    <p class="org-unit__empty">Data Kaur belum tersedia.</p>
                                @endif
                            </section>

                            <section class="org-unit org-unit--hamlets" aria-labelledby="org-hamlets-title">
                                <span class="org-line org-line--vertical org-unit__root-line" aria-hidden="true" style="--org-line-delay: 280ms"></span>
                                <h2 id="org-hamlets-title" class="org-unit__title">Kepala Dusun</h2>

                                @if (count($organization['hamletHeads']))
                                    <div class="org-unit__connector" aria-hidden="true" style="--org-connector-inset: {{ 50 / count($organization['hamletHeads']) }}%">
                                        <span class="org-line org-line--vertical" style="--org-line-delay: 430ms"></span>
                                        <span class="org-line org-line--horizontal" style="--org-line-delay: 510ms"></span>
                                    </div>
                                    <div class="org-unit__nodes org-unit__nodes--hamlets" style="--org-columns: {{ count($organization['hamletHeads']) }}">
                                        @foreach ($organization['hamletHeads'] as $official)
                                            @include('pages.partials.organization-node', [
                                                'official' => $official,
                                                'size' => 'compact',
                                                'delay' => 610 + ($loop->index * 75),
                                            ])
                                        @endforeach
                                    </div>
                                @else
                                    <p class="org-unit__empty">Data Kepala Dusun belum tersedia.</p>
                                @endif
                            </section>
                        </div>
                    </div>
                </div>

                <p class="org-chart__hint">
                    <i class="fas fa-hand-pointer" aria-hidden="true"></i>
                    Pada perangkat sentuh, ketuk foto atau jabatan untuk membuka nama.
                </p>

                @if (count($organization['others']))
                    <section class="org-other-officials" aria-labelledby="org-other-title">
                        <h2 id="org-other-title">Perangkat Lainnya</h2>
                        <div class="org-other-officials__grid">
                            @foreach ($organization['others'] as $official)
                                @include('pages.partials.organization-node', [
                                    'official' => $official,
                                    'delay' => 0,
                                ])
                            @endforeach
                        </div>
                    </section>
                @endif
            </section>

            <x-profile-comment-section :context="$commentContext" />
        </div>
    </div>
</x-layouts.app>
