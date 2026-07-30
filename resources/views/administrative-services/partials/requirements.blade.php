<header class="administration-section-header">
    <h2 id="administration-requirements-heading">Persyaratan Pelayanan</h2>
    <p>Pilih jenis pelayanan untuk melihat dokumen yang perlu disiapkan.</p>
</header>

<div class="administration-search" role="search">
    <label class="screen-reader-text" for="administration-service-search">Cari jenis pelayanan</label>
    <i class="fas fa-search" aria-hidden="true"></i>
    <input
        id="administration-service-search"
        class="administration-search__field"
        type="search"
        placeholder="Cari jenis pelayanan..."
        autocomplete="off"
        data-administration-search
    >
    <button
        class="administration-search__clear"
        type="button"
        aria-label="Hapus pencarian"
        title="Hapus pencarian"
        data-administration-search-clear
        hidden
    >
        <i class="fas fa-times" aria-hidden="true"></i>
    </button>
</div>

<section
    class="administration-accordion"
    aria-label="Daftar layanan administrasi"
    data-sidebar-accordion
    data-service-accordion
>
    @foreach ($services as $service)
        @include('administrative-services.partials.service-accordion', [
            'service' => $service,
            'open' => $loop->first,
        ])
    @endforeach
</section>

@include('administrative-services.partials.empty-search')
