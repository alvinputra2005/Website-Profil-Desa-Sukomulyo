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
            <iframe
                title="Peta 3D Kantor Desa Sukomulyo"
                src="https://www.google.com/maps?q=Kantor%20Desa%20Sukomulyo%2C%20Kecamatan%20Pujon%2C%20Kabupaten%20Malang&amp;t=k&amp;z=18&amp;output=embed"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
        </div>
            <div class="village-office-copy">
                <p><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Sukomulyo, Kecamatan Pujon, Kabupaten Malang</p>
                <a
                    href="https://www.google.com/maps/search/?api=1&amp;query=Kantor+Desa+Sukomulyo+Kecamatan+Pujon+Kabupaten+Malang"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Lihat Street View &amp; Rute <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </section>

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
