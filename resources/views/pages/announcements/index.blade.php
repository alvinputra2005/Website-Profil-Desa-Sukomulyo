<x-layouts.app
    title="Pengumuman Desa"
    description="Informasi resmi dan pengumuman terbaru dari Pemerintah Desa Sukomulyo."
    :canonical="route('announcements.index')"
>
    <x-page-header
        title="Pengumuman Desa"
        :breadcrumbs="[
            ['label' => 'Informasi Desa', 'url' => route('informasi-publik-desa')],
            ['label' => 'Pengumuman Desa'],
        ]"
    />

    <div class="announcement-page">
        <div class="container">
            <x-announcements.toolbar :filters="$filters" />

            @if ($announcements->total() > 0)
                <p class="announcement-results-meta" aria-live="polite">
                    Menampilkan {{ $announcements->firstItem() }}–{{ $announcements->lastItem() }}
                    dari {{ $announcements->total() }} pengumuman
                </p>

                <div class="announcement-table">
                    <div class="announcement-table-head" aria-hidden="true">
                        <span>Tanggal</span>
                        <span>Pengumuman</span>
                        <span>Aksi</span>
                    </div>

                    <div class="announcement-list">
                        @foreach ($announcements as $announcement)
                            <x-announcements.card :announcement="$announcement" />
                        @endforeach
                    </div>
                </div>

                @if ($announcements->hasPages())
                    <nav class="announcement-pagination" aria-label="Halaman pengumuman">
                        {{ $announcements->links() }}
                    </nav>
                @endif
            @else
                <x-announcements.empty-state :search="$filters['search']" />
            @endif
        </div>
    </div>
</x-layouts.app>
