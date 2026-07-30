@php
    $publishedDate = $announcement->published_at ?? $announcement->created_at;
    $description = $announcement->seo_description ?: ($announcement->excerpt ?: Str::limit(strip_tags($announcement->content), 160));
    $totalDownloads = (int) ($announcement->total_downloads ?? $announcement->attachments->sum('download_count'));
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
                <span class="announcement-category">Pengumuman Desa</span>
                <h1>{{ $announcement->title }}</h1>
            </header>

            <div class="announcement-detail-grid">
                <section class="announcement-body" aria-labelledby="announcement-body-title">
                    <h2 id="announcement-body-title" class="screen-reader-text">Isi pengumuman</h2>
                    {!! $announcement->content !!}
                </section>

                <aside class="announcement-sidebar" aria-label="Informasi dokumen">
                    <h2>Informasi Dokumen</h2>
                    <dl>
                        <div>
                            <dt>Diterbitkan</dt>
                            <dd>{{ $publishedDate->format('d/m/Y, H:i') }} WIB</dd>
                        </div>
                        @if ($announcement->start_date || $announcement->end_date)
                            <div>
                                <dt>Masa berlaku</dt>
                                <dd>
                                    {{ $announcement->start_date?->format('d/m/Y') ?: 'Sejak diterbitkan' }}
                                    @if ($announcement->end_date)
                                        – {{ $announcement->end_date->format('d/m/Y') }}
                                    @endif
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt>Jumlah lampiran</dt>
                            <dd>{{ $announcement->attachments->count() }} PDF</dd>
                        </div>
                        <div>
                            <dt>Total unduhan</dt>
                            <dd>{{ number_format($totalDownloads, 0, ',', '.') }} kali</dd>
                        </div>
                    </dl>
                </aside>
            </div>

            <x-announcements.attachment-list :announcement="$announcement" />

            <a class="announcement-back-link" href="{{ route('announcements.index') }}">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Kembali ke Daftar Pengumuman
            </a>
        </div>
    </article>
</x-layouts.app>
