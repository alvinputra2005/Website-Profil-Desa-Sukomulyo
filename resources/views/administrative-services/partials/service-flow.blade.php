<section class="administration-sidebar-widget administration-flow-widget">
    <h2 class="administration-sidebar-heading">
        <button
            class="administration-sidebar-toggle"
            type="button"
            aria-expanded="true"
            aria-controls="administration-service-flow"
            data-sidebar-toggle
            data-sidebar-accordion-toggle
        >
            <span>
                <i class="fas fa-route" aria-hidden="true"></i>
                Alur Pelayanan
            </span>
            <i class="fas fa-chevron-down administration-sidebar-chevron" aria-hidden="true"></i>
        </button>
    </h2>

    <div
        id="administration-service-flow"
        class="administration-sidebar-content"
        data-sidebar-panel
    >
        <ol class="service-flow-list">
            @foreach ($serviceFlow as $step)
                <li class="service-flow-item">
                    <span class="service-flow-number" aria-hidden="true">{{ $step['number'] }}</span>
                    <div class="service-flow-content">
                        <span class="service-flow-actor">
                            <i class="{{ $step['icon'] }}" aria-hidden="true"></i>
                            {{ $step['actor'] }}
                        </span>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['description'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
