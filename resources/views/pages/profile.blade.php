<x-layouts.app
    title="Identitas Desa"
    :description="'Informasi lengkap identitas, wilayah, pemerintahan, dan karakter '.$site['name'].'.'">
    <x-page-header title="Identitas Desa" :breadcrumbs="[
        ['label' => 'Profile Desa'],
        ['label' => 'Identitas Desa'],
    ]" />

    @php
        $shareUrl = route('profile-desa');
        $shareText = 'Identitas Desa Sukomulyo - '.$site['name'];
        $villageRows = collect($identityGroups)->flatMap(fn ($group) => $group['rows'])->keyBy('label');
        $villageAddress = $villageRows->get('Alamat Kantor Desa')['value'] ?? $site['address'];
        $villageHead = $villageRows->get('Nama Kepala Desa')['value'] ?? $villageLeader['name'];
    @endphp

    <div class="container news-detail-container profile-detail-container">
        <div id="sc_innerpage_wrap" class="news-detail-layout profile-article-layout" data-hide-back-to-top data-disable-scroll-reveal>
            <article class="sc_innerpage_contentbx single-article village-profile-article">
                <header class="entry-header">
                    <h1 class="entry-title">Identitas Desa Sukomulyo</h1>
                    <div class="postmeta" aria-label="Informasi artikel">
                        <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>Diperbarui {{ now()->translatedFormat('d F Y') }}</span>
                        <span class="post-author"><i class="far fa-user" aria-hidden="true"></i>Pemerintah Desa Sukomulyo</span>
                        <button class="profile-print-button" type="button" data-print-article>
                            <i class="fas fa-print" aria-hidden="true"></i>Cetak Artikel
                        </button>
                    </div>
                </header>

                <figure class="news-detail-hero profile-detail-hero">
                    <img src="{{ asset('assets/balai-desa-sukomulyo.jpeg') }}" alt="Balai Desa Sukomulyo">
                    <figcaption>Balai Desa Sukomulyo, pusat pelayanan pemerintahan dan masyarakat desa.</figcaption>
                </figure>

                <div class="article-reading-body">
                    <div class="entry-content">
                        <h2>Gambaran Umum Desa</h2>
                        <p>
                            Desa Sukomulyo adalah kesatuan masyarakat hukum yang memiliki batas wilayah dan berwenang mengatur kepentingan masyarakat setempat berdasarkan prakarsa masyarakat serta ketentuan peraturan perundang-undangan. Pusat pelayanan pemerintahan desa beralamat di {{ $villageAddress ?: 'Kantor Desa Sukomulyo' }}.
                        </p>
                        <p>
                            Pemerintahan desa dipimpin oleh {{ $villageHead ?: 'Kepala Desa Sukomulyo' }} bersama perangkat desa. Pelayanan diarahkan agar warga memperoleh informasi, administrasi, dan pendampingan secara ramah, terbuka, serta dapat dipertanggungjawabkan.
                        </p>

                    </div>
                </div>

                <x-profile-comment-section :context="$commentContext" />
            </article>

            <x-profile-share :url="$shareUrl" :text="$shareText" label="artikel Identitas Desa" />

            <x-profile-sidebar :leader="$villageLeader" :regulations="$villageRegulations" :latest-comments="$latestComments" />
        </div>
    </div>
</x-layouts.app>
