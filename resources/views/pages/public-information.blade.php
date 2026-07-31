<x-layouts.app title="Informasi Publik Desa">
    <x-page-header title="Informasi Publik Desa" description="Akses dokumen, pengumuman, dan layanan informasi Pemerintah Desa Sukomulyo." />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth">
                <div id="informasi-publik" class="public-info-intro">
                    <div>
                        <span class="section-kicker">Keterbukaan Informasi</span>
                        <h2>Dokumen Publik Desa</h2>
                        <p>Pemerintah desa menyediakan informasi publik secara terbuka agar masyarakat dapat mengetahui perencanaan, anggaran, pelayanan, dan pelaksanaan pembangunan desa.</p>
                    </div>
                    <a class="learnmore" href="{{ route('kontak.index') }}">Ajukan Permohonan Informasi</a>
                </div>

                <div class="document-list">
                    @foreach ($documents as $document)
                        <article class="document-card">
                            <span class="document-icon"><i class="{{ $document['icon'] }}" aria-hidden="true"></i></span>
                            <div>
                                <p>{{ $document['category'] }} · {{ $document['year'] }}</p>
                                <h3>{{ $document['title'] }}</h3>
                            </div>
                            <button type="button" aria-label="Dokumen {{ $document['title'] }} belum tersedia" title="Dokumen akan ditambahkan setelah data resmi tersedia">
                                <i class="fas fa-download" aria-hidden="true"></i><span>Unduh</span>
                            </button>
                        </article>
                    @endforeach
                </div>

                <section class="information-services">
                    <h2>Akses Informasi Desa</h2>
                    <div class="value-grid">
                        <article id="pengumuman-desa"><i class="fas fa-bullhorn" aria-hidden="true"></i><h3><a href="{{ route('announcements.index') }}">Pengumuman Desa</a></h3><p>Informasi resmi dan pengumuman penting dari pemerintah desa.</p></article>
                        <article id="layanan-administrasi"><i class="fas fa-id-card" aria-hidden="true"></i><h3>Syarat Administrasi</h3><p>Persyaratan surat, jadwal pelayanan, dan alur pelayanan masyarakat.</p></article>
                        <article id="agenda-desa"><i class="fas fa-calendar-alt" aria-hidden="true"></i><h3>Agenda Desa</h3><p>Jadwal kegiatan desa, musyawarah, dan kegiatan masyarakat.</p></article>
                        <article id="informasi-bantuan-sosial"><i class="fas fa-hands-helping" aria-hidden="true"></i><h3>Informasi Bantuan Sosial</h3><p>Jadwal bantuan, syarat penerima, dan informasi penyaluran.</p></article>
                    </div>
                </section>
            </section>
        </div>
    </div>
</x-layouts.app>
