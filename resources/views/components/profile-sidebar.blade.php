@props([
    'leader',
    'regulations' => [],
    'latestComments' => [],
])

@php
    $comments = collect($latestComments)->take(3);
@endphp

<aside id="sidebar" class="profile-article-sidebar" aria-label="Informasi profil desa" data-profile-accordion>
    <section class="profile-side-widget leader-profile-card">
        <h2 class="profile-side-title profile-widget-heading">
            <button
                class="profile-widget-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="profile-leader-panel"
                data-sidebar-toggle
                data-profile-widget-toggle
            >
                <span>Profil Pimpinan</span>
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
            </button>
        </h2>
        <div id="profile-leader-panel" class="profile-widget-panel" hidden>
            <div class="leader-card-copy">
                <p class="leader-role">{{ $leader['role'] }}</p>
                <h3>{{ $leader['name'] }}</h3>
                <p class="leader-greeting">{{ $leader['greeting'] }}</p>
                <a class="leader-read-more" href="{{ route('pemerintahan-desa') }}">
                    Baca Profil <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </section>

    <section class="profile-side-widget regulation-widget">
        <h2 class="profile-side-title profile-widget-heading">
            <button
                class="profile-widget-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="profile-regulations-panel"
                data-sidebar-toggle
                data-profile-widget-toggle
            >
                <span>Peraturan Desa</span>
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
            </button>
        </h2>
        <div id="profile-regulations-panel" class="profile-widget-panel" hidden>
            <div class="regulation-grid">
            @foreach ($regulations as $regulation)
                @if ($regulation['url'])
                    <a class="regulation-card" href="{{ $regulation['url'] }}" target="_blank" rel="noopener noreferrer">
                @else
                    <div class="regulation-card regulation-card--placeholder">
                @endif
                    <span class="regulation-pdf-icon" aria-hidden="true"><i class="fas fa-file-pdf"></i></span>
                    <span class="regulation-copy">
                        <strong>{{ $regulation['title'] }}</strong>
                        <small>{{ $regulation['number'] }} · {{ $regulation['year'] }}</small>
                    </span>
                @if ($regulation['url'])
                    </a>
                @else
                    </div>
                @endif
            @endforeach
            </div>
        </div>
    </section>

    <section class="profile-side-widget village-office-widget">
        <h2 class="profile-side-title profile-widget-heading">
            <button
                class="profile-widget-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="profile-office-panel"
                data-sidebar-toggle
                data-profile-widget-toggle
            >
                <span>Kantor Desa</span>
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
            </button>
        </h2>
        <div id="profile-office-panel" class="profile-widget-panel" hidden>
            <div class="village-office-map">
                <button class="village-office-map-preview" type="button" data-streetview-open aria-haspopup="dialog" aria-controls="village-streetview-dialog">
                    <img
                        src="https://streetviewpixels-pa.googleapis.com/v1/thumbnail?cb_client=maps_sv.tactile&amp;w=900&amp;h=600&amp;pitch=0&amp;panoid=Y-35hbv_lCuAzBEo5ThHsg&amp;yaw=344.88394"
                        alt="Tampilan depan Kantor Desa Sukomulyo"
                        loading="lazy"
                    >
                    <span><i class="fas fa-street-view" aria-hidden="true"></i>Buka Kamera 360°</span>
                </button>
            </div>
            <div class="village-office-copy">
                <p><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Sukomulyo, Kecamatan Pujon, Kabupaten Malang</p>
                <a
                    href="https://maps.app.goo.gl/uefh2BNpF7VGxTEh7"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Lihat Street View &amp; Rute <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </section>

    <dialog id="village-streetview-dialog" class="village-streetview-dialog" aria-label="Kamera 360 derajat Kantor Desa Sukomulyo" data-streetview-dialog>
        <div class="village-streetview-dialog-header">
            <strong>Kamera 360° Kantor Desa</strong>
            <button type="button" aria-label="Tutup kamera 360 derajat" data-streetview-close><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
        <iframe
            title="Street View 360 derajat Kantor Desa Sukomulyo"
            data-streetview-frame
            data-src="https://www.google.com/maps?q=Balai%20Desa%20Sukomulyo&amp;layer=c&amp;cbll=-7.8670608,112.4408184&amp;cbp=12,344.88,0,0,0&amp;output=svembed"
            loading="lazy"
            allowfullscreen
            referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
    </dialog>

    <section class="profile-side-widget latest-comments-widget">
        <h2 class="profile-side-title profile-widget-heading">
            <button
                class="profile-widget-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="profile-comments-panel"
                data-sidebar-toggle
                data-profile-widget-toggle
            >
                <span>Komentar Terbaru</span>
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
            </button>
        </h2>
        <div id="profile-comments-panel" class="profile-widget-panel" hidden>
            <div class="latest-comment-list">
            @forelse ($comments as $comment)
                @php
                    $name = data_get($comment, 'name');
                    $commentId = data_get($comment, 'id');
                    $commentDate = data_get($comment, 'created_at');
                    $likes = data_get($comment, 'like_count', data_get($comment, 'likes', 0));
                @endphp
                <article class="latest-comment">
                    <span class="latest-comment-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span>
                    <div>
                        <h3>{{ $name }}</h3>
                        <p>{{ data_get($comment, 'comment') }}</p>
                        <div class="latest-comment-meta">
                            <time datetime="{{ $commentDate->toIso8601String() }}">{{ $commentDate->diffForHumans() }}</time>
                            @if ($commentId)
                                <form action="{{ route('profile-desa.comments.like', $commentId) }}" method="POST">
                                    @csrf
                                    <button class="latest-comment-likes" type="submit" aria-label="Sukai komentar {{ $name }}">
                                        <i class="far fa-thumbs-up" aria-hidden="true"></i>{{ $likes }}
                                    </button>
                                </form>
                            @else
                                <span class="latest-comment-likes"><i class="far fa-thumbs-up" aria-hidden="true"></i>{{ $likes }}</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <p class="latest-comment-empty">Belum ada komentar pada halaman ini.</p>
            @endforelse
            </div>
        </div>
    </section>
</aside>
