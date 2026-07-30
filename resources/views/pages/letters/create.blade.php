<x-layouts.app :title="'Ajukan '.$letterService->name" robots="noindex, nofollow">
    <x-page-header :title="'Ajukan '.$letterService->name" description="Isi data sesuai dokumen resmi. Data hanya digunakan untuk memproses permohonan ini." :show-heading="true" :breadcrumbs="[['label'=>'Pelayanan Surat','url'=>route('letter-services.index')],['label'=>$letterService->name],['label'=>'Isi Data Pemohon']]" />
    <div class="letter-service-page letter-form-page">
        <div class="container">
            <ol class="letter-steps" aria-label="Tahapan pengajuan surat">
                @foreach(['Pilih Jenis Surat', 'Isi Data Pemohon', 'Unggah Dokumen', 'Konfirmasi'] as $step)
                    <li @class(['is-active' => $loop->iteration <= 2]) @if($loop->iteration === 2) aria-current="step" @endif>
                        <span>{{ $loop->iteration }}</span><strong>{{ $step }}</strong>
                    </li>
                @endforeach
            </ol>

            <div class="letter-form-layout">
                <main>
                    <div class="letter-form-heading">
                        <span class="section-kicker">Tahap 2 dari 4</span>
                        <h2>Isi Data Pemohon</h2>
                        <p>Lengkapi data berikut sesuai dokumen resmi. Kolom bertanda <b>*</b> wajib diisi.</p>
                    </div>
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
                <aside class="letter-form-sidebar">
                    <section class="letter-info-card letter-form-summary">
                        <span class="letter-form-summary-icon"><i class="fas fa-file-signature" aria-hidden="true"></i></span>
                        <h2>{{ $letterService->name }}</h2>
                        <p>{{ $letterService->description }}</p>
                        <dl>
                            <div><dt>Estimasi selesai</dt><dd>{{ $letterService->processing_days }} hari kerja</dd></div>
                            <div><dt>Biaya layanan</dt><dd>{{ $letterService->fee_information }}</dd></div>
                        </dl>
                    </section>
                    <section class="letter-info-card letter-form-tip">
                        <h2><i class="fas fa-lightbulb" aria-hidden="true"></i> Tips pengisian</h2>
                        <ul>
                            <li>Pastikan NIK berjumlah 16 digit.</li>
                            <li>Gunakan nomor WhatsApp yang aktif.</li>
                            <li>Periksa kembali data sebelum melanjutkan.</li>
                        </ul>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</x-layouts.app>
