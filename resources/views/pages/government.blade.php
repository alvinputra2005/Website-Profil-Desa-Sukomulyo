<x-layouts.app title="Struktur Pemerintahan" description="Struktur organisasi Pemerintah Desa Sukomulyo.">
    <x-page-header title="Struktur Pemerintahan" description="Struktur organisasi Pemerintah Desa Sukomulyo." :breadcrumbs="[
        ['label' => 'Profile Desa', 'url' => route('profile-desa')],
        ['label' => 'Struktur Pemerintahan'],
    ]" />

    <div class="government-page" data-hide-back-to-top data-disable-scroll-reveal>
        <div class="container government-page__container">
            <section class="government-organization" aria-label="Bagan struktur organisasi Pemerintah Desa Sukomulyo">
                <figure class="government-structure-image">
                    <img
                        src="{{ asset('assets/gambar-struktur-pemerintahan.png') }}"
                        alt="Bagan struktur organisasi Pemerintah Desa Sukomulyo"
                        width="1545"
                        height="769"
                        loading="eager"
                        fetchpriority="high"
                    >
                </figure>
            </section>

            <x-profile-comment-section :context="$commentContext" />
        </div>
    </div>
</x-layouts.app>
