@php
    $serviceFlowPanelId = $serviceFlowPanelId ?? 'administration-service-flow';
    $serviceFlowExpanded = $serviceFlowExpanded ?? true;
@endphp

<section class="administration-sidebar-widget administration-flow-widget">
    <h2 class="administration-sidebar-heading">
        <button
            class="administration-sidebar-toggle"
            type="button"
            aria-expanded="{{ $serviceFlowExpanded ? 'true' : 'false' }}"
            aria-controls="{{ $serviceFlowPanelId }}"
            data-sidebar-toggle
            data-sidebar-accordion-toggle
        >
            <span>
                Alur Pelayanan
            </span>
            <i class="fas fa-chevron-down administration-sidebar-chevron" aria-hidden="true"></i>
        </button>
    </h2>

    <div
        id="{{ $serviceFlowPanelId }}"
        class="administration-sidebar-content"
        data-sidebar-panel
        @if(! $serviceFlowExpanded) hidden @endif
    >
        <ol class="service-flow-list">
            @foreach ($serviceFlow as $step)
                <li class="service-flow-item">
                    <span class="service-flow-number" aria-hidden="true">{{ $step['number'] }}</span>
                    <div class="service-flow-content">
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['description'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
