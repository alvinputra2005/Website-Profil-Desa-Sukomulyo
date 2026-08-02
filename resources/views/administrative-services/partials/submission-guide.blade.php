@php
    $submissionGuidePanelId = $submissionGuidePanelId ?? 'administration-submission-guide';
    $submissionGuideAsFlow = $submissionGuideAsFlow ?? false;
@endphp

<section class="administration-sidebar-widget administration-guide-widget">
    <h2 class="administration-sidebar-heading">
        <button
            class="administration-sidebar-toggle"
            type="button"
            aria-expanded="false"
            aria-controls="{{ $submissionGuidePanelId }}"
            data-sidebar-toggle
            data-sidebar-accordion-toggle
        >
            <span>
                Tata Cara Pengajuan
            </span>
            <i class="fas fa-chevron-down administration-sidebar-chevron" aria-hidden="true"></i>
        </button>
    </h2>

    <div
        id="{{ $submissionGuidePanelId }}"
        class="administration-sidebar-content"
        data-sidebar-panel
        hidden
    >
        @if($submissionGuideAsFlow)
            <ol class="service-flow-list submission-guide-flow">
                @foreach ($submissionSteps as $step)
                    <li class="service-flow-item">
                        <span class="service-flow-number" aria-hidden="true">{{ $loop->iteration }}</span>
                        <div class="service-flow-content">
                            <h3>{{ $step }}</h3>
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <ol class="submission-guide-list">
                @foreach ($submissionSteps as $step)
                    <li>
                        <span aria-hidden="true">{{ $loop->iteration }}</span>
                        <p>{{ $step }}</p>
                    </li>
                @endforeach
            </ol>
        @endif

    </div>
</section>
