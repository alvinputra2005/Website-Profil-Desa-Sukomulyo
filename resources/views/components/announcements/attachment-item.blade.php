@props(['announcement', 'attachment'])

@php
    $media = $attachment->media;
    $size = (int) $media->file_size;
    $formattedSize = $size >= 1048576
        ? number_format($size / 1048576, 1, ',', '.').' MB'
        : number_format(max($size, 1) / 1024, 0, ',', '.').' KB';
@endphp

<li class="announcement-attachment">
    <span class="announcement-attachment-icon" aria-hidden="true">
        <i class="fas fa-file-pdf"></i>
    </span>
    <div class="announcement-attachment-content">
        <h3>{{ $attachment->title ?: $media->original_name }}</h3>
        <p>
            PDF · {{ $formattedSize }} · Diunduh {{ number_format($attachment->download_count, 0, ',', '.') }} kali
        </p>
    </div>
    <div class="announcement-attachment-actions">
        <a
            class="announcement-button announcement-button--view"
            href="{{ route('announcements.attachments.preview', ['publication' => $announcement->slug, 'attachment' => $attachment->id]) }}"
            target="_blank"
            rel="noopener"
            aria-label="Lihat PDF {{ $attachment->title ?: $media->original_name }} di tab baru"
        >
            <i class="far fa-eye" aria-hidden="true"></i>
            <span>Lihat PDF</span>
        </a>
    </div>
</li>
