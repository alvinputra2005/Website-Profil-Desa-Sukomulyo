<section class="administration-office-card" aria-labelledby="administration-office-heading">
    <header>
        <span class="administration-office-icon" aria-hidden="true">
            <i class="fas fa-building"></i>
        </span>
        <div>
            <span class="administration-office-kicker">Kantor Desa Sukomulyo</span>
            <h2 id="administration-office-heading">Informasi Pelayanan</h2>
        </div>
    </header>

    <dl class="administration-office-list">
        <div>
            <dt><i class="far fa-calendar-alt" aria-hidden="true"></i> Hari pelayanan</dt>
            <dd>{{ $office['days'] }}</dd>
        </div>
        <div>
            <dt><i class="far fa-clock" aria-hidden="true"></i> Jam pelayanan</dt>
            <dd>{{ $office['hours'] }}</dd>
        </div>
        <div>
            <dt><i class="fas fa-coffee" aria-hidden="true"></i> Waktu istirahat</dt>
            <dd>{{ $office['break'] }}</dd>
        </div>
        <div>
            <dt><i class="fas fa-door-closed" aria-hidden="true"></i> Hari libur</dt>
            <dd>{{ $office['closed'] }}</dd>
        </div>
        <div>
            <dt><i class="fas fa-hourglass-half" aria-hidden="true"></i> Estimasi Dukcapil</dt>
            <dd>{{ $office['processing_estimate'] }}</dd>
        </div>
    </dl>

    <div class="administration-free-badge">
        <i class="fas fa-check-circle" aria-hidden="true"></i>
        <span><strong>{{ $office['fee'] }}</strong> {{ $office['fee_description'] }}</span>
    </div>

    <a
        class="administration-whatsapp"
        href="{{ $whatsappUrl }}"
        target="_blank"
        rel="noopener noreferrer"
    >
        <i class="fab fa-whatsapp" aria-hidden="true"></i>
        <span>
            <small>Hubungi layanan</small>
            {{ $office['whatsapp_display'] }}
        </span>
    </a>
</section>
