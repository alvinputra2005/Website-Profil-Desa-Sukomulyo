@props(['announcement'])

@php
    $date = $announcement->published_at ?? $announcement->created_at;
    $primaryAttachment = $announcement->attachments->first();
@endphp

<article class="announcement-card">
    <x-announcements.date-badge :date="$date" />

    <div class="announcement-content">
        <h2 class="announcement-title">
            <a href="{{ route('announcements.show', ['publication' => $announcement->slug]) }}">{{ $announcement->title }}</a>
        </h2>
        @if ($announcement->excerpt)
            <p class="announcement-excerpt">{{ $announcement->excerpt }}</p>
        @endif
    </div>

    <div class="announcement-actions">
        <a class="announcement-button announcement-button--view" href="{{ route('announcements.show', ['publication' => $announcement->slug]) }}">
            <i class="far fa-eye" aria-hidden="true"></i>
            <span>Lihat Pengumuman</span>
        </a>
        @if ($primaryAttachment)
            <a
                class="announcement-button announcement-button--download"
                href="{{ route('announcements.attachments.download', ['publication' => $announcement->slug, 'attachment' => $primaryAttachment->id]) }}"
            >
                <i class="fas fa-download" aria-hidden="true"></i>
                <span>Unduh PDF</span>
            </a>
        @else
            <span class="announcement-no-attachment">
                <i class="fas fa-paperclip" aria-hidden="true"></i>
                Tanpa Lampiran
            </span>
        @endif
    </div>
</article>
