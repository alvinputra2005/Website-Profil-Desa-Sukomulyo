<x-layouts.app title="Halaman Tidak Ditemukan">
    <div class="not-found-page">
        <div class="container">
            <span class="error-code">404</span>
            <h1>Halaman Tidak Ditemukan</h1>
            <p>Alamat yang Anda buka tidak tersedia atau sudah dipindahkan.</p>
            <div class="not-found-actions">
                <a class="learnmore" href="{{ route('home') }}"><i class="fas fa-home" aria-hidden="true"></i> Kembali ke Beranda</a>
                <a class="button" href="{{ route('search') }}">Cari Berita</a>
            </div>
        </div>
    </div>
</x-layouts.app>
