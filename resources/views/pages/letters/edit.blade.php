<x-layouts.app title="Perbaiki Permohonan" robots="noindex, nofollow">
    <x-page-header title="Perbaiki Permohonan" :description="$application->application_number" :show-heading="true" :breadcrumbs="[['label'=>'Pelayanan Surat','url'=>route('letter-services.index')],['label'=>'Perbaiki Permohonan']]" />
    <div class="container"><div id="sc_innerpage_wrap"><section class="letter-section"><div class="narrow"><div class="letter-notice warning"><i class="fas fa-exclamation-circle"></i><div><strong>Catatan petugas</strong><p>{{ $application->public_note }}</p></div></div><form class="letter-form letter-card" method="post" action="{{ route('letter-services.application.update', $token) }}">@csrf @method('PUT')
        @include('pages.letters.partials.form-fields')
        <label class="letter-check"><input type="checkbox" name="declaration" value="1" required><span>Saya menyatakan data perbaikan ini benar.</span></label><input type="hidden" name="website" value=""><button class="letter-button" type="submit">Kirim Perbaikan</button>
    </form></div></section></div></div>
</x-layouts.app>
