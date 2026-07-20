<x-layouts.app title="Pemerintahan Desa">
    <x-page-header title="Pemerintahan Desa" description="Struktur organisasi dan prinsip pelayanan Pemerintah Desa Sukomulyo." />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth">
                <div class="content-intro">
                    <span class="section-kicker">Struktur Organisasi</span>
                    <h2>Perangkat Desa Sukomulyo</h2>
                    <p>Pemerintah desa menjalankan pelayanan, administrasi, pembangunan, dan pemberdayaan masyarakat sesuai tugas masing-masing.</p>
                </div>

                <div class="official-grid">
                    @foreach ($officials as $official)
                        <article class="official-card">
                            <div class="official-avatar"><i class="fas fa-user" aria-hidden="true"></i></div>
                            <div>
                                <p>{{ $official['role'] }}</p>
                                <h3>{{ $official['name'] }}</h3>
                            </div>
                        </article>
                    @endforeach
                </div>

                <section class="service-values">
                    <h2>Komitmen Pelayanan</h2>
                    <div class="value-grid">
                        <article><i class="fas fa-handshake" aria-hidden="true"></i><h3>Melayani</h3><p>Mendahulukan kebutuhan masyarakat dengan ramah dan responsif.</p></article>
                        <article><i class="fas fa-eye" aria-hidden="true"></i><h3>Transparan</h3><p>Menyampaikan program dan informasi publik secara terbuka.</p></article>
                        <article><i class="fas fa-balance-scale" aria-hidden="true"></i><h3>Akuntabel</h3><p>Menjalankan tugas dengan tertib dan dapat dipertanggungjawabkan.</p></article>
                    </div>
                </section>
            </section>
        </div>
    </div>
</x-layouts.app>
