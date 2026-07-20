<x-layouts.app title="Informasi Publik Desa">
    <x-page-header title="Informasi Publik Desa" description="Akses dokumen, pengumuman, dan layanan informasi Pemerintah Desa Sukomulyo." />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth">
                <div class="public-info-intro">
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
                    <h2>Layanan Informasi</h2>
                    <div class="value-grid">
                        <article><i class="fas fa-id-card" aria-hidden="true"></i><h3>Administrasi Penduduk</h3><p>Informasi persyaratan pengantar administrasi kependudukan.</p></article>
                        <article><i class="fas fa-certificate" aria-hidden="true"></i><h3>Surat Keterangan</h3><p>Informasi pengajuan surat keterangan melalui kantor desa.</p></article>
                        <article><i class="fas fa-comments" aria-hidden="true"></i><h3>Permohonan Informasi</h3><p>Saluran pertanyaan dan permintaan data publik desa.</p></article>
                    </div>
                </section>
            </section>
        </div>
    </div>
</x-layouts.app>
