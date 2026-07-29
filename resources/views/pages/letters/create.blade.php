<x-layouts.app :title="'Ajukan '.$letterService->name" robots="noindex, nofollow">
    <x-page-header :title="'Ajukan '.$letterService->name" description="Isi data sesuai dokumen resmi. Data hanya digunakan untuk memproses permohonan ini." :show-heading="true" :breadcrumbs="[['label'=>'Pelayanan Surat','url'=>route('letter-services.index')],['label'=>$letterService->name,'url'=>route('letter-services.show',$letterService)],['label'=>'Ajukan Permohonan']]" />
    <div class="container"><div id="sc_innerpage_wrap"><section class="letter-section"><div class="narrow letter-form-heading"><span class="section-kicker">Formulir Permohonan</span><h2>Data Pemohon</h2><p>Kolom bertanda wajib harus diisi dengan data yang benar dan dapat dipertanggungjawabkan.</p></div><div class="narrow"><form class="letter-form letter-card" method="post" action="{{ route('letter-services.application.store', $letterService) }}">@csrf
        <input type="hidden" name="submission_key" value="{{ $submissionKey }}">@include('pages.letters.partials.form-fields', ['application' => null])
        <label class="letter-check"><input type="checkbox" name="declaration" value="1" @checked(old('declaration')) required><span>Saya menyatakan data yang diisi benar dan bersedia membawa dokumen asli saat diperlukan.</span></label>
        <div class="letter-honeypot" aria-hidden="true"><label for="website">Website</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off"></div>
        <button class="letter-button" type="submit">Kirim Permohonan</button>
    </form></div></section></div></div>
</x-layouts.app>
