<x-layouts.app title="Struktur Pemerintahan" description="Struktur organisasi Pemerintah Desa Sukomulyo beserta perangkat desa dan kepala dusun.">
    <x-page-header title="Struktur Pemerintahan" description="Struktur organisasi Pemerintah Desa Sukomulyo beserta perangkat desa dan kepala dusun." :breadcrumbs="[
        ['label' => 'Profile Desa', 'url' => route('profile-desa')],
        ['label' => 'Struktur Pemerintahan'],
    ]" />

    <div class="government-page" data-hide-back-to-top data-disable-scroll-reveal>
        <div class="container government-page__container">
            <section class="government-organization" aria-labelledby="government-organization-title">
                <header class="government-organization__intro">
                    <span class="section-kicker">Pemerintah Desa</span>
                    <h1 id="government-organization-title">Struktur Pemerintahan</h1>
                    <p>Struktur organisasi Pemerintah Desa Sukomulyo beserta perangkat desa dan kepala dusun.</p>
                </header>

                <div class="government-organization-chart-shell">
                    <div data-government-organization-root>
                        <p class="government-organization-loading" role="status" data-government-organization-status>
                            <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                            Memuat struktur pemerintahan interaktif...
                        </p>
                    </div>
                    <noscript>
                        <p class="government-organization-noscript">Aktifkan JavaScript untuk melihat struktur pemerintahan interaktif.</p>
                    </noscript>
                </div>
            </section>

            <x-profile-comment-section :context="$commentContext" />
        </div>
    </div>
</x-layouts.app>
