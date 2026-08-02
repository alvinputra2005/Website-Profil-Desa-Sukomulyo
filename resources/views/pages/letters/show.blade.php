<x-layouts.app :title="$letterService->name" :description="$letterService->description">
    <x-page-header
        :title="$letterService->name"
        :description="$letterService->description"
        :show-heading="true"
        :breadcrumbs="[
            ['label' => 'Pelayanan'],
            ['label' => 'Pengajuan Layanan', 'url' => route('letter-services.index')],
            ['label' => $letterService->name],
        ]"
    />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="letter-section letter-two-column">
                <div class="letter-card">
                    <span class="section-kicker">Persyaratan</span>
                    <h2>Dokumen yang perlu disiapkan</h2>
                    <ul class="letter-requirements">
                        @foreach($letterService->requirements_json as $requirement)
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span>
                                    <strong>{{ $requirement['label'] }}</strong>
                                    @if($requirement['description'] ?? null)
                                        <small>{{ $requirement['description'] }}</small>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <aside class="letter-card letter-summary">
                    <h3>Informasi layanan</h3>
                    <span class="letter-summary-icon"><i class="fas fa-file-signature"></i></span>
                    <p><strong>Estimasi:</strong> {{ $letterService->processing_days }} hari kerja</p>
                    <p><strong>Biaya:</strong> {{ $letterService->fee_information }}</p>
                    <p><strong>Jam pelayanan:</strong> {{ $settings->officeHours() }}</p>
                    <p><strong>Pengambilan:</strong> {{ $settings->pickupAddress() }}</p>
                    @if($letterService->pickup_instructions)
                        <p>{{ $letterService->pickup_instructions }}</p>
                    @endif
                    <a class="letter-button" href="{{ route('letter-services.application.create', $letterService) }}">
                        Ajukan Permohonan Surat <i class="fas fa-arrow-right"></i>
                    </a>
                    <small>Surat diberikan secara fisik dan tidak tersedia untuk diunduh.</small>
                </aside>
            </section>
        </div>
    </div>
</x-layouts.app>
