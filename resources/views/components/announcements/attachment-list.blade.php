@props(['announcement'])

<section class="announcement-attachments" aria-labelledby="announcement-attachments-title">
    <div class="announcement-section-heading">
        <div>
            <span class="section-kicker">Dokumen Resmi</span>
            <h2 id="announcement-attachments-title">Lampiran Pengumuman</h2>
        </div>
        <span>{{ $announcement->attachments->count() }} lampiran</span>
    </div>

    @if ($announcement->attachments->isNotEmpty())
        <ul class="announcement-attachment-list">
            @foreach ($announcement->attachments as $attachment)
                <x-announcements.attachment-item :announcement="$announcement" :attachment="$attachment" />
            @endforeach
        </ul>
    @else
        <div class="announcement-attachment-empty">
            <i class="fas fa-paperclip" aria-hidden="true"></i>
            <p>Pengumuman ini tidak memiliki lampiran PDF.</p>
        </div>
    @endif
</section>
