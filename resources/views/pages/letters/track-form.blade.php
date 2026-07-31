<x-layouts.app title="Lacak Permohonan Surat" robots="noindex, nofollow">
    <x-page-header title="Lacak Permohonan Surat" description="Pantau tahapan resmi permohonan surat Anda melalui website desa." :show-heading="true" :breadcrumbs="[['label'=>'Pelayanan'],['label'=>'Pengajuan Layanan','url'=>route('letter-services.index')],['label'=>'Lacak Permohonan']]" />
    <div class="container"><div id="sc_innerpage_wrap"><section class="letter-section"><div class="narrow letter-track-panel"><div class="letter-track-icon"><i class="fas fa-search-location"></i></div><div class="letter-form-heading"><span class="section-kicker">Pelacakan Aman</span><h2>Masukkan data pelacakan</h2><p>Gunakan nomor permohonan dan PIN enam digit yang diterima saat pengajuan.</p></div><form class="letter-form letter-card" method="post" action="{{ route('letter-services.track.store') }}">@csrf
        @error('application_number')<div class="letter-notice danger">{{ $message }}</div>@enderror
        <label>Nomor permohonan<input name="application_number" value="{{ old('application_number') }}" placeholder="PS-SKU-20260729-ABC123" required></label>
        <label>PIN pelacakan<input name="pin" inputmode="numeric" minlength="6" maxlength="6" required></label>
        <button class="letter-button" type="submit">Lacak Status</button>
    </form></div></section></div></div>
</x-layouts.app>
