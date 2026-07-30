@props(['search' => null])

<div class="announcement-empty">
    <span class="announcement-empty-icon">
        <i class="fas {{ $search ? 'fa-search' : 'fa-bullhorn' }}" aria-hidden="true"></i>
    </span>
    <h2>{{ $search ? 'Pengumuman Tidak Ditemukan' : 'Belum Ada Pengumuman' }}</h2>
    @if ($search)
        <p>Tidak ada pengumuman yang sesuai dengan kata kunci “{{ $search }}”.</p>
        <a class="announcement-button announcement-button--view" href="{{ route('announcements.index') }}">
            Hapus Pencarian
        </a>
    @else
        <p>Pengumuman resmi Pemerintah Desa Sukomulyo akan ditampilkan di halaman ini.</p>
    @endif
</div>
