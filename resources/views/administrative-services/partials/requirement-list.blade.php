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
</div>
