@php
    $section = (string) request()->route('section', '');
    $activeMenu = (string) request('menu', '');
    $isFamilyPage = request()->routeIs('data-statistik.detail') && $section === 'keluarga';
    $familyMenu = $isFamilyPage ? $activeMenu : '';
    $populationExpanded = ! $isFamilyPage;
    $familyExpanded = $isFamilyPage;
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
                        <a class="{{ request()->routeIs('data-statistik.detail') && $section === 'pendidikan' ? 'is-active' : '' }}" href="{{ route('data-statistik.detail', ['section' => 'pendidikan']) }}">
                            Pendidikan
                        </a>
                    </li>
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
                    <li><a class="{{ $activeMenu === 'rentang-umur' ? 'is-active' : '' }}" href="{{ $populationUrl('rentang-umur') }}">Rentang Umur</a></li>
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
                    <li><a class="{{ $isFamilyPage && $familyMenu === '' ? 'is-active' : '' }}" href="{{ route('data-statistik.detail', ['section' => 'keluarga']) }}">Ringkasan Keluarga</a></li>
                    <li><a class="{{ $familyMenu === 'kepala-keluarga' ? 'is-active' : '' }}" href="{{ route('data-statistik.detail', ['section' => 'keluarga', 'menu' => 'kepala-keluarga']) }}">Kepala Keluarga</a></li>
                    <li><a class="{{ $familyMenu === 'anggota-keluarga' ? 'is-active' : '' }}" href="{{ route('data-statistik.detail', ['section' => 'keluarga', 'menu' => 'anggota-keluarga']) }}">Anggota Keluarga</a></li>
                    <li><a class="{{ $familyMenu === 'rumah-tangga' ? 'is-active' : '' }}" href="{{ route('data-statistik.detail', ['section' => 'keluarga', 'menu' => 'rumah-tangga']) }}">Rumah Tangga</a></li>
                </ul>
            </div>
        </section>
    </nav>
</aside>
