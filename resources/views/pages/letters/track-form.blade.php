<x-layouts.app title="Lacak Permohonan Surat" robots="noindex, nofollow">
    <section class="letter-hero compact"><div class="container"><h2>Lacak Permohonan Surat</h2><p>Masukkan nomor permohonan dan PIN enam digit yang diterima saat pengajuan.</p></div></section>
    <section class="letter-section"><div class="container narrow"><form class="letter-form letter-card" method="post" action="{{ route('letter-services.track.store') }}">@csrf
        @error('application_number')<div class="letter-notice danger">{{ $message }}</div>@enderror
        <label>Nomor permohonan<input name="application_number" value="{{ old('application_number') }}" placeholder="PS-SKU-20260729-ABC123" required></label>
        <label>PIN pelacakan<input name="pin" inputmode="numeric" minlength="6" maxlength="6" required></label>
        <button class="letter-button" type="submit">Lacak Status</button>
    </form></div></section>
</x-layouts.app>
