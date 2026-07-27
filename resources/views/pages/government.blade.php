<x-layouts.app title="Struktur Pemerintahan" description="Struktur organisasi Pemerintah Desa Sukomulyo.">
    <x-page-header title="Struktur Pemerintahan" description="Struktur organisasi Pemerintah Desa Sukomulyo." :breadcrumbs="[
        ['label' => 'Profile Desa', 'url' => route('profile-desa')],
        ['label' => 'Struktur Pemerintahan'],
    ]" />

    <div class="government-page" data-hide-back-to-top data-disable-scroll-reveal>
        <div class="container government-page__container">
            <section
                class="government-organization"
                aria-labelledby="government-organization-title"
            >
                <header class="government-organization__intro">
                    <span class="section-kicker">Struktur Organisasi</span>
                    <h1 id="government-organization-title">Pemerintahan Desa Sukomulyo</h1>
                    <p>Struktur ditampilkan berdasarkan hubungan jabatan perangkat desa.</p>
                </header>

                <div class="public-organization-scroll" tabindex="0" aria-label="Bagan organisasi Pemerintah Desa Sukomulyo">
                    <div class="public-organization-chart">
                        @forelse ($nodes as $node)
                            @include('pages.partials.organization-tree-node', ['node' => $node])
                        @empty
                            <p class="public-organization-empty">Data perangkat desa aktif belum tersedia.</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <x-profile-comment-section :context="$commentContext" />
        </div>
    </div>
</x-layouts.app>
