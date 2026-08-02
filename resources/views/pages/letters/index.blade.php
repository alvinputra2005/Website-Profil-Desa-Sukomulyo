<x-layouts.app title="Pengajuan Layanan" description="Layanan administrasi daring Desa Sukomulyo.">
    <x-page-header title="Pengajuan Layanan" :show-heading="false" :breadcrumbs="[['label'=>'Pelayanan'],['label'=>'Pengajuan Layanan']]" />
    @php
        $firstService = $services->first();
        $whatsappNumber = $settings->whatsappNumber();
    @endphp
    <div class="letter-service-page" data-letter-selector>
        <div class="container">
            <header class="letter-service-heading">
                <h1>Pengajuan Layanan</h1>
            </header>

            <ol class="letter-steps" aria-label="Tahapan pengajuan layanan">
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
                                <a class="letter-choice {{ $loop->first ? 'is-selected' : '' }} {{ $service->code === 'SKTM' ? 'letter-choice--featured' : '' }}" href="{{ route('letter-services.application.create', $service) }}">
                                    <span class="letter-choice-icon"><i class="fas {{ $service->icon ?: config('letter_services.default_icon') }}" aria-hidden="true"></i></span>
                                    <span class="letter-choice-copy">
                                        <strong>{{ $service->name }}</strong>
                                        @if ($service->code === 'SKTM')
                                            <small>Untuk SKTM dan kebutuhan administrasi lainnya.</small>
                                        @endif
                                    </span>
                                    <i class="fas fa-chevron-right letter-choice-arrow" aria-hidden="true"></i>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="letter-notice">Belum ada layanan surat yang aktif.</div>
                    @endif
                </section>

                <aside class="letter-info-sidebar" aria-label="Panduan dan informasi pelayanan surat" data-sidebar-accordion>
                    @include('administrative-services.partials.submission-guide', [
                        'submissionGuidePanelId' => 'letter-index-submission-guide',
                        'submissionGuideAsFlow' => true,
                    ])

                    <section class="letter-info-card letter-info-card--help" data-static-info-card>
                        <header class="letter-info-card__heading">
                            <h2>Butuh Bantuan?</h2>
                        </header>
                        <div class="letter-info-card__content">
                            <p>Hubungi petugas Desa Sukomulyo melalui WhatsApp untuk informasi lebih lanjut.</p>
                            @if($whatsappNumber)
                                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp" aria-hidden="true"></i> Hubungi via WhatsApp</a>
                            @else
                                <a href="{{ route('kontak.index') }}"><i class="fas fa-envelope" aria-hidden="true"></i> Hubungi Petugas</a>
                            @endif
                        </div>
                    </section>

                    <section class="letter-info-card letter-info-card--hours" data-static-info-card>
                        <header class="letter-info-card__heading">
                            <h2>Jam Layanan</h2>
                        </header>
                        <div class="letter-info-card__content letter-info-card__content--hours">
                            <span class="letter-info-card__feature-icon"><i class="far fa-clock" aria-hidden="true"></i></span>
                            <div>
                                <strong>Hari dan jam pelayanan</strong>
                                <p>{{ $settings->officeHours() }}</p>
                            </div>
                        </div>
                    </section>

                </aside>
            </div>
        </div>
    </div>
</x-layouts.app>
