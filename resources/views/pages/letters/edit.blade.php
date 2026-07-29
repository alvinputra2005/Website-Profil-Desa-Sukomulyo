<x-layouts.app title="Perbaiki Permohonan" robots="noindex, nofollow">
    <section class="letter-hero compact"><div class="container"><h2>Perbaiki Permohonan</h2><p>{{ $application->application_number }}</p></div></section>
    <section class="letter-section"><div class="container narrow"><div class="letter-notice warning"><strong>Catatan petugas:</strong><br>{{ $application->public_note }}</div><form class="letter-form letter-card" method="post" action="{{ route('letter-services.application.update', $token) }}">@csrf @method('PUT')
        @include('pages.letters.partials.form-fields')
        <label class="letter-check"><input type="checkbox" name="declaration" value="1" required><span>Saya menyatakan data perbaikan ini benar.</span></label><input type="hidden" name="website" value=""><button class="letter-button" type="submit">Kirim Perbaikan</button>
    </form></div></section>
</x-layouts.app>
