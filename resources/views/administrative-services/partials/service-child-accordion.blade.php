<section
    class="service-child-item"
    data-service-child-item
    data-service-search="{{ $child['search_text'] }}"
>
    <h4 class="service-child-heading">
        <button
            class="service-child-toggle"
            type="button"
            aria-expanded="{{ $open ? 'true' : 'false' }}"
            aria-controls="service-child-panel-{{ $child['id'] }}"
            data-sidebar-toggle
            data-sidebar-accordion-toggle
        >
            <span>{{ $child['title'] }}</span>
            <i class="fas fa-chevron-down service-child-chevron" aria-hidden="true"></i>
        </button>
    </h4>

    <div
        id="service-child-panel-{{ $child['id'] }}"
        class="service-child-panel"
        data-sidebar-panel
        @if (! $open) hidden @endif
    >
        @include('administrative-services.partials.requirement-list', [
            'requirements' => $child['requirements'],
            'notes' => $child['notes'] ?? [],
        ])
    </div>
</section>
