@props(['announcement', 'showAttachmentCount' => true])

@php
    $date = $announcement->published_at ?? $announcement->created_at;
    $attachmentCount = (int) ($announcement->attachments_count ?? $announcement->attachments->count());
    $downloadCount = (int) ($announcement->total_downloads ?? $announcement->attachments->sum('download_count'));
@endphp

<div class="announcement-meta">
    <span>
        <i class="far fa-calendar-alt" aria-hidden="true"></i>
        Diterbitkan {{ $date->format('d/m/Y') }}
    </span>
    @if ($showAttachmentCount)
        <span>
            <i class="fas fa-paperclip" aria-hidden="true"></i>
            {{ $attachmentCount }} lampiran
        </span>
    @endif
    <span>
        <i class="fas fa-download" aria-hidden="true"></i>
        Diunduh {{ number_format($downloadCount, 0, ',', '.') }} kali
    </span>
</div>
