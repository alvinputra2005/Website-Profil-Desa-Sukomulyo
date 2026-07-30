<section class="administration-sidebar-widget administration-guide-widget">
    <h2 class="administration-sidebar-heading">
        <button
            class="administration-sidebar-toggle"
            type="button"
            aria-expanded="false"
            aria-controls="administration-submission-guide"
            data-sidebar-toggle
            data-sidebar-accordion-toggle
        >
            <span>
                <i class="fas fa-list-ol" aria-hidden="true"></i>
                Tata Cara Pengajuan
            </span>
            <i class="fas fa-chevron-down administration-sidebar-chevron" aria-hidden="true"></i>
        </button>
    </h2>

    <div
        id="administration-submission-guide"
        class="administration-sidebar-content"
        data-sidebar-panel
        hidden
    >
        <ol class="submission-guide-list">
            @foreach ($submissionSteps as $step)
                <li>
                    <span aria-hidden="true">{{ $loop->iteration }}</span>
                    <p>{{ $step }}</p>
                </li>
            @endforeach
        </ol>

        <p class="administration-guide-notice">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <span>{{ $submissionNotice }}</span>
        </p>
    </div>
</section>
