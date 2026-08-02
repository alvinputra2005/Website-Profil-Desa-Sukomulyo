<div class="service-requirements">
    <p class="service-requirements-label">Dokumen yang perlu disiapkan:</p>
    <ol class="requirement-list">
        @foreach ($requirements as $requirement)
            <li class="requirement-list__item">
                <span class="requirement-list__number" aria-hidden="true">{{ $loop->iteration }}</span>
                <span>{{ $requirement }}</span>
            </li>
        @endforeach
    </ol>
    @if (! empty($notes))
        <div class="service-requirement-notes">
            @foreach ($notes as $note)
                <p><i class="fas fa-info-circle" aria-hidden="true"></i><span>{{ $note }}</span></p>
            @endforeach
        </div>
    @endif
</div>
