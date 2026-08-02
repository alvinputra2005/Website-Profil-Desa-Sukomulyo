<section class="letter-info-card letter-info-card--tracking" data-static-info-card>
    <header class="letter-info-card__heading">
        <h2><i class="fas fa-search" aria-hidden="true"></i> Lacak Status Pengajuan</h2>
    </header>

    <form class="letter-info-card__content letter-tracking-quick-form" method="post" action="{{ route('letter-services.track.store') }}">
        @csrf
        <p>Masukkan nomor pelacakan untuk melihat status pengajuan terbaru.</p>

        <label for="quick-tracking-application-number">
            <span>Nomor pelacakan</span>
            <input
                id="quick-tracking-application-number"
                name="application_number"
                value="{{ old('application_number') }}"
                placeholder="PS-SKU-20260729-ABC123"
                autocomplete="off"
                required
            >
        </label>
        @error('application_number')
            <small class="letter-tracking-quick-error">{{ $message }}</small>
        @enderror

        <button type="submit"><i class="fas fa-search" aria-hidden="true"></i> Lihat Status</button>
    </form>
</section>
