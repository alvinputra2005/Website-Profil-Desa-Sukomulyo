@php
    $section = (string) request()->route('section', '');
    $activeMenu = (string) request('menu', '');
    $isFamilyPage = request()->routeIs('data-statistik.detail') && $section === 'keluarga';
    $populationExpanded = ! $isFamilyPage;
    $familyExpanded = $isFamilyPage;
    $ageExpanded = in_array($section, ['kelompok-umur'], true) || str_starts_with($activeMenu, 'rentang-umur');

    $populationUrl = fn (string $menu): string => route('data-statistik.population', ['menu' => $menu]);
@endphp

<aside id="sidebar" class="statistics-sidebar" aria-label="Navigasi data statistik" data-no-scroll-reveal>
    <nav class="statistics-sidebar__nav" data-sidebar-accordion>
        <section class="statistics-sidebar__group">
            <h2 class="statistics-sidebar__heading">
                <button
                    class="statistics-sidebar__toggle"
                    type="button"
                    aria-expanded="{{ $populationExpanded ? 'true' : 'false' }}"
                    aria-controls="statistics-population-panel"
                    data-sidebar-toggle
                    data-sidebar-accordion-toggle
                >
                    <span class="statistics-sidebar__icon statistics-sidebar__icon--population" aria-hidden="true">
                        <i class="fas fa-users"></i>
                    </span>
                    <span>Statistik Penduduk</span>
                    <i class="fas fa-chevron-down statistics-sidebar__chevron" aria-hidden="true"></i>
                </button>
            </h2>

            <div id="statistics-population-panel" class="statistics-sidebar__panel" @if (! $populationExpanded) hidden @endif>
                <ul class="statistics-sidebar__list">
                    <li>
                        <a class="{{ $activeMenu === 'agama' ? 'is-active' : '' }}" href="{{ $populationUrl('agama') }}">
                            Agama
                        </a>
                    </li>
                    <li><a class="{{ $activeMenu === 'akte-kelahiran' ? 'is-active' : '' }}" href="{{ $populationUrl('akte-kelahiran') }}">Akte Kelahiran</a></li>
                    <li><a class="{{ $activeMenu === 'akseptor-kb' ? 'is-active' : '' }}" href="{{ $populationUrl('akseptor-kb') }}">Akseptor KB</a></li>
                    <li><a class="{{ $activeMenu === 'penyandang-cacat' ? 'is-active' : '' }}" href="{{ $populationUrl('penyandang-cacat') }}">Penyandang Cacat</a></li>
                    <li><a class="{{ $activeMenu === 'golongan-darah' ? 'is-active' : '' }}" href="{{ $populationUrl('golongan-darah') }}">Golongan Darah</a></li>
                    <li>
                        <a class="{{ request()->routeIs('data-statistik.population') && $activeMenu === '' ? 'is-active' : '' }}" href="{{ route('data-statistik.population') }}">
                            Jenis Kelamin
                        </a>
                    </li>
                    <li>
                        <a class="{{ request()->routeIs('data-statistik.detail') && $section === 'pendidikan' && $activeMenu !== 'pendidikan-ditempuh' ? 'is-active' : '' }}" href="{{ route('data-statistik.detail', ['section' => 'pendidikan']) }}">
                            Pendidikan Dalam KK
                        </a>
                    </li>
                    <li><a class="{{ $activeMenu === 'pendidikan-ditempuh' ? 'is-active' : '' }}" href="{{ $populationUrl('pendidikan-ditempuh') }}">Pendidikan Sedang Ditempuh</a></li>
                    <li><a class="{{ $activeMenu === 'penyakit-menahun' ? 'is-active' : '' }}" href="{{ $populationUrl('penyakit-menahun') }}">Penyakit Menahun</a></li>
                    <li>
                        <a class="{{ request()->routeIs('data-statistik.detail') && in_array($section, ['pekerjaan', 'ekonomi'], true) ? 'is-active' : '' }}" href="{{ route('data-statistik.detail', ['section' => 'pekerjaan']) }}">
                            Pekerjaan
                        </a>
                    </li>
                    <li><a class="{{ $activeMenu === 'status-penduduk' ? 'is-active' : '' }}" href="{{ $populationUrl('status-penduduk') }}">Status Penduduk</a></li>
                    <li>
                        <a class="{{ $activeMenu === 'status-perkawinan' ? 'is-active' : '' }}" href="{{ $populationUrl('status-perkawinan') }}">
                            Status Perkawinan
                        </a>
                    </li>
                    <li class="statistics-sidebar__nested">
                        <button
                            class="statistics-sidebar__nested-toggle {{ $ageExpanded ? 'is-active' : '' }}"
                            type="button"
                            aria-expanded="{{ $ageExpanded ? 'true' : 'false' }}"
                            aria-controls="statistics-age-panel"
                            data-sidebar-toggle
                        >
                            <span>Rentang Umur</span>
                            <i class="fas fa-caret-right" aria-hidden="true"></i>
                        </button>
                        <ul id="statistics-age-panel" class="statistics-sidebar__sublist" @if (! $ageExpanded) hidden @endif>
                            <li><a href="{{ $populationUrl('rentang-umur-anak') }}">Anak (0–14 tahun)</a></li>
                            <li><a href="{{ $populationUrl('rentang-umur-muda') }}">Usia Muda (15–24 tahun)</a></li>
                            <li><a href="{{ $populationUrl('rentang-umur-dewasa') }}">Dewasa (25–64 tahun)</a></li>
                            <li><a href="{{ $populationUrl('rentang-umur-lansia') }}">Lansia (65+ tahun)</a></li>
                        </ul>
                    </li>
                    <li>
                        <a class="{{ $activeMenu === 'kategori-umur' ? 'is-active' : '' }}" href="{{ $populationUrl('kategori-umur') }}">
                            Kategori Umur
                        </a>
                    </li>
                    <li><a class="{{ $activeMenu === 'wajib-ktp' ? 'is-active' : '' }}" href="{{ $populationUrl('wajib-ktp') }}">Kepemilikan Wajib KTP</a></li>
                    <li><a class="{{ $activeMenu === 'warga-negara' ? 'is-active' : '' }}" href="{{ $populationUrl('warga-negara') }}">Warga Negara</a></li>
                </ul>
            </div>
        </section>

        <section class="statistics-sidebar__group">
            <h2 class="statistics-sidebar__heading">
                <button
                    class="statistics-sidebar__toggle"
                    type="button"
                    aria-expanded="{{ $familyExpanded ? 'true' : 'false' }}"
                    aria-controls="statistics-family-panel"
                    data-sidebar-toggle
                    data-sidebar-accordion-toggle
                >
                    <span class="statistics-sidebar__icon statistics-sidebar__icon--family" aria-hidden="true">
                        <i class="fas fa-home"></i>
                    </span>
                    <span>Statistik Keluarga</span>
                    <i class="fas fa-chevron-down statistics-sidebar__chevron" aria-hidden="true"></i>
                </button>
            </h2>
            <div id="statistics-family-panel" class="statistics-sidebar__panel" @if (! $familyExpanded) hidden @endif>
                <ul class="statistics-sidebar__list">
                    <li><a class="{{ $isFamilyPage ? 'is-active' : '' }}" href="{{ route('data-statistik.detail', ['section' => 'keluarga']) }}">Ringkasan Keluarga</a></li>
                    <li><a href="{{ route('data-statistik.detail', ['section' => 'keluarga', 'menu' => 'kepala-keluarga']) }}">Kepala Keluarga</a></li>
                    <li><a href="{{ route('data-statistik.detail', ['section' => 'keluarga', 'menu' => 'anggota-keluarga']) }}">Anggota Keluarga</a></li>
                    <li><a href="{{ route('data-statistik.detail', ['section' => 'keluarga', 'menu' => 'rumah-tangga']) }}">Rumah Tangga</a></li>
                </ul>
            </div>
        </section>

        <section class="statistics-sidebar__group">
            <h2 class="statistics-sidebar__heading">
                <button
                    class="statistics-sidebar__toggle"
                    type="button"
                    aria-expanded="false"
                    aria-controls="statistics-assistance-panel"
                    data-sidebar-toggle
                    data-sidebar-accordion-toggle
                >
                    <span class="statistics-sidebar__icon statistics-sidebar__icon--assistance" aria-hidden="true">
                        <i class="fas fa-hand-holding-heart"></i>
                    </span>
                    <span>Statistik Bantuan</span>
                    <i class="fas fa-chevron-down statistics-sidebar__chevron" aria-hidden="true"></i>
                </button>
            </h2>
            <div id="statistics-assistance-panel" class="statistics-sidebar__panel" hidden>
                <ul class="statistics-sidebar__list">
                    <li><a href="{{ route('informasi-desa.detail', ['section' => 'bantuan-sosial']) }}">Informasi Bantuan Sosial</a></li>
                    <li><a href="{{ route('data-statistik.detail', ['section' => 'ekonomi', 'menu' => 'penerima-bantuan']) }}">Penerima Bantuan</a></li>
                    <li><a href="{{ route('data-statistik.detail', ['section' => 'ekonomi', 'menu' => 'keluarga-penerima']) }}">Keluarga Penerima</a></li>
                </ul>
            </div>
        </section>
    </nav>
</aside>
