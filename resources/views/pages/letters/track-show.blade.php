<x-layouts.app title="Status Permohonan Surat" robots="noindex, nofollow">
    <x-page-header
        title="Status Permohonan Surat"
        :show-heading="false"
        :breadcrumbs="[
            ['label' => 'Pelayanan'],
            ['label' => 'Pengajuan Layanan', 'url' => route('letter-services.index')],
            ['label' => 'Status Permohonan'],
        ]"
    />

    <div class="container letter-status-page-container">
        <div id="sc_innerpage_wrap">
            <section class="letter-section">
                <h1 class="letter-status-page-title">Status Permohonan Surat</h1>

                <div class="letter-status-heading">
                    <div>
                        <p class="letter-application-label">Nomor permohonan</p>
                        <p class="letter-application-number">{{ $application->application_number }}</p>
                    </div>
                    <span class="letter-status-badge">{{ $application->status->label() }}</span>
                </div>

                <ol class="letter-steps letter-steps--complete" aria-label="Tahapan pengajuan layanan selesai">
                    @foreach(['Pilih Jenis Surat', 'Isi Data Pemohon', 'Unggah Dokumen', 'Konfirmasi'] as $step)
                        <li class="is-active" @if($loop->last) aria-current="step" @endif>
                            <span aria-hidden="true"><i class="fas fa-check"></i></span>
                            <strong>{{ $step }}</strong>
                        </li>
                    @endforeach
                </ol>

                <div class="letter-two-column letter-status-layout">
                    <div class="letter-status-main">
                        @if(session('new_application'))
                            <div class="letter-notice success">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <h3>Permohonan berhasil disimpan</h3>
                                    <p>Simpan nomor permohonan dan PIN ini. PIN hanya ditampilkan sekarang.</p>
                                    <p class="letter-pin">{{ session('tracking_pin') }}</p>
                                    <p>Agar petugas segera mengetahui pengajuan Anda, buka WhatsApp Desa dan kirim pesan yang telah disiapkan.</p>
                                </div>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="letter-notice success">{{ session('success') }}</div>
                        @endif

                        @if($application->public_note)
                            <div class="letter-notice warning">
                                <strong>Catatan petugas</strong>
                                <p>{{ $application->public_note }}</p>
                            </div>
                        @endif

                        <div class="letter-card letter-status-summary">
                            <h3>Ringkasan</h3>
                            <p><strong>Pemohon:</strong> {{ $application->maskedName() }}</p>
                            <p><strong>Diajukan:</strong> {{ $application->submitted_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H.i') }} WIB</p>
                            <p><strong>Estimasi:</strong> {{ $application->service_snapshot_json['processing_days'] }} hari kerja</p>
                            <p><strong>Pengambilan:</strong> {{ $settings->pickupAddress() }}</p>
                            <p><strong>Jam:</strong> {{ $settings->officeHours() }}</p>

                            @if($application->status === \App\Enums\LetterApplicationStatus::ReadyForPickup)
                                <div class="letter-notice success">Surat siap diambil. Bawa dokumen asli dan nomor permohonan.</div>
                            @endif

                            @if($token)
                                <div class="letter-status-actions">
                                    <form method="post" action="{{ route('letter-services.whatsapp.confirm', $token) }}" target="_blank">
                                        @csrf
                                        <button class="letter-button whatsapp" type="submit">
                                            <i class="fab fa-whatsapp"></i> Konfirmasi ke WhatsApp Desa <i class="fas fa-external-link-alt"></i>
                                        </button>
                                    </form>

                                    @if($application->status->canTransitionTo(\App\Enums\LetterApplicationStatus::Cancelled))
                                        <form method="post" action="{{ route('letter-services.application.cancel', $token) }}" onsubmit="return confirm('Batalkan permohonan ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button class="letter-button danger" type="submit">Batalkan Permohonan</button>
                                        </form>
                                    @endif
                                </div>
                                <small>WhatsApp dibuka di tab baru. Jika tidak terbuka, izinkan pop-up untuk situs ini. Anda tetap harus menekan tombol Kirim di WhatsApp.</small>
                            @endif

                            @if($token && $application->canBeEditedByApplicant())
                                <a class="letter-button secondary" href="{{ route('letter-services.application.edit', $token) }}">Perbaiki Permohonan</a>
                            @endif

                            <p class="letter-no-download">Surat tidak tersedia dalam bentuk file dan tidak dapat diunduh.</p>
                        </div>
                    </div>

                    <aside class="letter-card letter-status-history">
                        <h3>Riwayat status</h3>
                        <ol class="letter-timeline">
                            @foreach($application->statusHistories as $history)
                                <li>
                                    <span></span>
                                    <div>
                                        <strong>{{ $history->to_status->label() }}</strong>
                                        <small>{{ $history->created_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H.i') }} WIB</small>
                                        @if($history->public_note)
                                            <p>{{ $history->public_note }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </aside>
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
