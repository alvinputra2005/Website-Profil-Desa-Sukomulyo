<x-layouts.app title="Pengajuan Surat" description="Layanan pengajuan surat Desa Sukomulyo.">
    <x-page-header title="Pengajuan Surat" :show-heading="false" :breadcrumbs="[['label'=>'Pelayanan'],['label'=>'Pengajuan Surat']]" />
    @php
        $serviceIcons = [
            'SKU' => 'fa-store', 'SKD' => 'fa-home', 'SKTM' => 'fa-hand-holding-heart',
            'SKCK' => 'fa-file-alt', 'SKBM' => 'fa-user', 'SKL' => 'fa-baby', 'SKM' => 'fa-ribbon',
        ];
        $firstService = $services->first();
        $whatsappNumber = $settings->whatsappNumber();
    @endphp
    <div class="letter-service-page" data-letter-selector>
        <div class="container">
            <header class="letter-service-heading">
                <h1>Pengajuan Surat</h1>
            </header>

            <ol class="letter-steps" aria-label="Tahapan pengajuan surat">
                @foreach(['Pilih Jenis Surat', 'Isi Data Pemohon', 'Unggah Dokumen', 'Konfirmasi'] as $step)
                    <li @class(['is-active' => $loop->first]) @if($loop->first) aria-current="step" @endif>
                        <span>{{ $loop->iteration }}</span><strong>{{ $step }}</strong>
                    </li>
                @endforeach
            </ol>

            <div class="letter-service-layout">
                <section class="letter-selector" aria-labelledby="letter-selector-title">
                    <div class="letter-selector-heading">
                        <h2 id="letter-selector-title">Pilih Jenis Surat</h2>
                        <p>Pilih jenis surat yang ingin Anda ajukan permohonannya.</p>
                    </div>

                    @if($services->isNotEmpty())
                        <div class="letter-choice-grid" role="radiogroup" aria-label="Jenis surat">
                            @foreach($services as $service)
                                <a class="letter-choice {{ $loop->first ? 'is-selected' : '' }}" href="{{ route('letter-services.application.create', $service) }}">
                                    <span class="letter-choice-icon"><i class="fas {{ $serviceIcons[$service->code] ?? 'fa-file-alt' }}" aria-hidden="true"></i></span>
                                    <span class="letter-choice-copy"><strong>{{ $service->name }}</strong><small>{{ $service->description }}</small></span>
                                    <i class="fas fa-chevron-right letter-choice-arrow" aria-hidden="true"></i>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="letter-notice">Belum ada layanan surat yang aktif.</div>
                    @endif
                </section>

                <aside class="letter-info-sidebar" aria-label="Informasi pelayanan surat">
                    <section class="letter-info-card letter-info-card--help">
                        <h2>Butuh Bantuan?</h2>
                        <p>Hubungi petugas Desa Sukomulyo melalui WhatsApp untuk informasi lebih lanjut.</p>
                        @if($whatsappNumber)
                            <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp" aria-hidden="true"></i> Hubungi via WhatsApp</a>
                        @else
                            <a href="{{ route('kontak.index') }}"><i class="fas fa-envelope" aria-hidden="true"></i> Hubungi Petugas</a>
                        @endif
                    </section>

                    <section class="letter-info-card letter-info-card--hours">
                        <span><i class="far fa-clock" aria-hidden="true"></i></span>
                        <div><h2>Jam Layanan</h2><p>{{ $settings->officeHours() }}</p></div>
                        <i class="far fa-calendar-alt letter-hours-icon" aria-hidden="true"></i>
                    </section>

                    <a class="letter-track-link" href="{{ route('letter-services.track.form') }}"><i class="fas fa-search" aria-hidden="true"></i> Lacak permohonan yang sudah diajukan</a>
                </aside>
            </div>
        </div>
    </div>
</x-layouts.app>
