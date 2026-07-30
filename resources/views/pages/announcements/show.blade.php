@php
    $description = $announcement->seo_description ?: ($announcement->excerpt ?: Str::limit(strip_tags($announcement->content), 160));
@endphp

<x-layouts.app
    :title="$announcement->seo_title ?: $announcement->title"
    :description="$description"
    :canonical="route('announcements.show', ['publication' => $announcement->slug])"
    og-type="article"
>
    <x-page-header
        :title="$announcement->title"
        :breadcrumbs="[
            ['label' => 'Informasi Desa', 'url' => route('informasi-publik-desa')],
            ['label' => 'Pengumuman Desa', 'url' => route('announcements.index')],
            ['label' => $announcement->title],
        ]"
    />

    <article class="announcement-detail">
        <div class="container">
            <header class="announcement-detail-header">
                <h1>{{ $announcement->title }}</h1>
            </header>

            <div class="announcement-detail-grid">
                <section class="announcement-body" aria-labelledby="announcement-body-title">
                    <h2 id="announcement-body-title" class="screen-reader-text">Isi pengumuman</h2>
                    {!! $announcement->content !!}
                </section>

                <x-announcements.attachment-list :announcement="$announcement" />
            </div>

            <a class="announcement-back-link" href="{{ route('announcements.index') }}">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Kembali ke Daftar Pengumuman
            </a>
        </div>
    </article>
</x-layouts.app>
