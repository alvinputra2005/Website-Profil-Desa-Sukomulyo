<x-layouts.app :title="'Ajukan '.$letterService->name" robots="noindex, nofollow">
    <section class="letter-hero compact"><div class="container"><a class="letter-back" href="{{ route('letter-services.show', $letterService) }}">← Kembali ke layanan</a><h2>Ajukan {{ $letterService->name }}</h2><p>Isi data sesuai dokumen resmi. Data Anda digunakan hanya untuk memproses permohonan ini.</p></div></section>
    <section class="letter-section"><div class="container narrow"><form class="letter-form letter-card" method="post" action="{{ route('letter-services.application.store', $letterService) }}">@csrf
        <input type="hidden" name="submission_key" value="{{ $submissionKey }}">@include('pages.letters.partials.form-fields', ['application' => null])
        <label class="letter-check"><input type="checkbox" name="declaration" value="1" @checked(old('declaration')) required><span>Saya menyatakan data yang diisi benar dan bersedia membawa dokumen asli saat diperlukan.</span></label>
        <div class="letter-honeypot" aria-hidden="true"><label for="website">Website</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off"></div>
        <button class="letter-button" type="submit">Kirim Permohonan</button>
    </form></div></section>
</x-layouts.app>
