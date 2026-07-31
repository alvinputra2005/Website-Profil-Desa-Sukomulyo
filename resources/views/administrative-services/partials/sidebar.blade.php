<div class="administration-sidebar-panel" data-sidebar-accordion>
    @include('administrative-services.partials.submission-guide', [
        'submissionGuideAsFlow' => true,
    ])
    @include('administrative-services.partials.application-cta')
    @include('administrative-services.partials.office-hours')
</div>
