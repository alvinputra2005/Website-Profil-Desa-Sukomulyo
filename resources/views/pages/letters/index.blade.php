<x-layouts.app title="Pelayanan Surat Desa" description="Ajukan permohonan awal surat desa dan pantau prosesnya.">
    <x-page-header title="Pelayanan Surat Desa" description="Ajukan permohonan awal, pantau prosesnya, lalu ambil surat fisik di Kantor Desa Sukomulyo." :show-heading="true" />
    <div class="container"><div id="sc_innerpage_wrap"><section class="letter-section">
        <div class="letter-intro"><div><span class="section-kicker">Layanan Administrasi</span><h2>Pilih jenis surat yang dibutuhkan</h2><p>Pelajari persyaratan setiap layanan sebelum mengajukan permohonan. Seluruh proses dan status resmi tercatat di website desa.</p></div><a class="letter-button secondary" href="{{ route('letter-services.track.form') }}"><i class="fas fa-search" aria-hidden="true"></i> Lacak Permohonan</a></div>
        <div class="letter-grid">
        @forelse($services as $service)
            <article class="letter-card"><span class="letter-code">{{ $service->code }}</span><h3>{{ $service->name }}</h3><p>{{ $service->description }}</p><dl><div><dt>Estimasi</dt><dd>{{ $service->processing_days }} hari kerja</dd></div><div><dt>Biaya</dt><dd>{{ $service->fee_information }}</dd></div></dl><a class="letter-link" href="{{ route('letter-services.show', $service) }}">Lihat persyaratan <span aria-hidden="true">→</span></a></article>
        @empty
            <div class="letter-notice">Belum ada layanan surat yang aktif.</div>
        @endforelse
        </div>
    </section></div></div>
</x-layouts.app>
