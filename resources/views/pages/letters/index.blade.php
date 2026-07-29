<x-layouts.app title="Pelayanan Surat Desa" description="Ajukan permohonan awal surat desa dan pantau prosesnya.">
    <section class="letter-hero"><div class="container"><span class="letter-kicker">Pelayanan Desa</span><h2>Pelayanan Surat Desa</h2><p>Ajukan permohonan awal melalui website, pantau prosesnya, lalu ambil surat fisik di Kantor Desa Sukomulyo setelah statusnya siap diambil.</p><a class="letter-button secondary" href="{{ route('letter-services.track.form') }}">Lacak Permohonan</a></div></section>
    <section class="letter-section"><div class="container"><div class="letter-grid">
        @forelse($services as $service)
            <article class="letter-card"><span class="letter-code">{{ $service->code }}</span><h3>{{ $service->name }}</h3><p>{{ $service->description }}</p><dl><div><dt>Estimasi</dt><dd>{{ $service->processing_days }} hari kerja</dd></div><div><dt>Biaya</dt><dd>{{ $service->fee_information }}</dd></div></dl><a class="letter-link" href="{{ route('letter-services.show', $service) }}">Lihat persyaratan <span aria-hidden="true">→</span></a></article>
        @empty
            <div class="letter-notice">Belum ada layanan surat yang aktif.</div>
        @endforelse
    </div></div></section>
</x-layouts.app>
