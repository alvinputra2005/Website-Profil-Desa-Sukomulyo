<x-layouts.app
    :title="$page['title']"
    :description="$page['description']"
    :canonical="route('informasi-desa.detail', ['section' => 'layanan-administrasi'])"
>
    <div
        id="administrative-service-ajax-root"
        class="administration-page"
        data-administrative-services
        data-ajax-scope="#administrative-service-ajax-root"
        data-page-title="{{ $page['title'] }} | {{ $site['name'] }}"
        data-page-description="{{ $page['description'] }}"
    >
        <x-page-header
            :title="$page['title']"
            :description="$page['description']"
            :show-heading="false"
            :breadcrumbs="[
                ['label' => 'Informasi Desa', 'url' => route('informasi-publik-desa')],
                ['label' => $page['title']],
            ]"
        />

        <div class="container administration-content-container">
            <div class="administration-page-layout">
                <section
                    id="administration-requirements"
                    class="administration-main"
                    aria-labelledby="administration-requirements-heading"
                >
                    @include('administrative-services.partials.requirements')
                </section>

                <aside
                    class="administration-sidebar"
                    aria-label="Informasi alur dan pengajuan pelayanan"
                >
                    @include('administrative-services.partials.sidebar')
                </aside>
            </div>
        </div>
    </div>
</x-layouts.app>
