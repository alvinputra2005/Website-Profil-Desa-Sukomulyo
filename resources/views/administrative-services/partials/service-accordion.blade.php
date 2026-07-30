<article
    class="service-accordion-item"
    data-service-item
    data-service-search="{{ $service['search_text'] }}"
    data-service-own-search="{{ $service['own_search_text'] }}"
>
    <h3 class="service-accordion-heading">
        <button
            class="service-accordion-toggle"
            type="button"
            aria-expanded="{{ $open ? 'true' : 'false' }}"
            aria-controls="service-panel-{{ $service['id'] }}"
            data-sidebar-toggle
            data-sidebar-accordion-toggle
        >
            <span class="service-accordion-icon" aria-hidden="true">
                <i class="{{ $service['icon'] }}"></i>
            </span>
            <span class="service-accordion-title">{{ $service['title'] }}</span>
            <i class="fas fa-chevron-down service-accordion-chevron" aria-hidden="true"></i>
        </button>
    </h3>

    <div
        id="service-panel-{{ $service['id'] }}"
        class="service-accordion-panel"
        data-sidebar-panel
        @if (! $open) hidden @endif
    >
        @if (isset($service['children']))
            <div
                class="service-child-accordion"
                data-sidebar-accordion
                data-service-child-accordion
            >
                @foreach ($service['children'] as $child)
                    @include('administrative-services.partials.service-child-accordion', [
                        'child' => $child,
                        'open' => $open && $loop->first,
                    ])
                @endforeach
            </div>
        @else
            @include('administrative-services.partials.requirement-list', [
                'requirements' => $service['requirements'],
                'notes' => $service['notes'] ?? [],
            ])
        @endif
    </div>
</article>
