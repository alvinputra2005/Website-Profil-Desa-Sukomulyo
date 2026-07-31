<x-layouts.app :title="'Ajukan '.$letterService->name" robots="noindex, nofollow">
    <x-page-header :title="'Ajukan '.$letterService->name" :show-heading="false" :breadcrumbs="[['label'=>'Pelayanan'],['label'=>'Pengajuan Layanan','url'=>route('letter-services.index')],['label'=>$letterService->name],['label'=>'Isi Data Pemohon']]" />
    <div class="letter-service-page letter-form-page">
        <div class="container">
            <header class="letter-service-heading">
                <h1>Ajukan {{ $letterService->name }}</h1>
                <p>Isi data sesuai dokumen resmi. Data hanya digunakan untuk memproses permohonan ini.</p>
            </header>

            <ol class="letter-steps" aria-label="Tahapan pengajuan layanan">
                @foreach(['Pilih Jenis Surat', 'Isi Data Pemohon', 'Unggah Dokumen', 'Konfirmasi'] as $step)
                    <li @class(['is-active' => $loop->iteration <= 2]) @if($loop->iteration === 2) aria-current="step" @endif>
                        <span>{{ $loop->iteration }}</span><strong>{{ $step }}</strong>
                    </li>
                @endforeach
            </ol>

            <div class="letter-form-layout">
                <main>
                    <form class="letter-form letter-card" method="post" action="{{ route('letter-services.application.store', $letterService) }}">
                        @csrf
                        <input type="hidden" name="submission_key" value="{{ $submissionKey }}">
                        @include('pages.letters.partials.form-fields', ['application' => null, 'hamlets' => $hamlets])
                        <label class="letter-check"><input type="checkbox" name="declaration" value="1" @checked(old('declaration')) required><span>Saya menyatakan data yang diisi benar dan bersedia membawa dokumen asli saat diperlukan.</span></label>
                        <div class="letter-honeypot" aria-hidden="true"><label for="website">Website</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off"></div>
                        <div class="letter-form-actions">
                            <a class="letter-form-back" href="{{ route('letter-services.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali pilih surat</a>
                            <button class="letter-button" type="submit">Lanjutkan <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                        </div>
                    </form>
                </main>
                <aside class="letter-form-sidebar administration-page" aria-label="Tata cara pengajuan dan jam pelayanan">
                    <div class="administration-sidebar-panel" data-sidebar-accordion>
                        @include('administrative-services.partials.submission-guide', [
                            'submissionGuidePanelId' => 'letter-submission-guide',
                            'submissionGuideAsFlow' => true,
                        ])
                        @include('administrative-services.partials.office-hours')
                    </div>
                </aside>
            </div>
        </div>
    </div>
</x-layouts.app>
