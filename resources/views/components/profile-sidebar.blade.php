@props([
    'leader',
    'regulations' => [],
    'latestComments' => [],
])

@php
    $dummyComments = collect([
        [
            'name' => 'Siti Aminah',
            'comment' => 'Informasi identitas desa sudah jelas dan sangat membantu warga.',
            'created_at' => now()->subHours(2),
            'likes' => 12,
        ],
        [
            'name' => 'Budi Santoso',
            'comment' => 'Semoga data dan layanan desa terus diperbarui seperti ini.',
            'created_at' => now()->subDays(1),
            'likes' => 8,
        ],
        [
            'name' => 'Rina Wulandari',
            'comment' => 'Tampilan informasinya rapi, jadi mudah dicari dari ponsel.',
            'created_at' => now()->subDays(2),
            'likes' => 5,
        ],
    ]);
    $comments = collect($latestComments)->take(3);
    $comments = $comments->concat($dummyComments->take(3 - $comments->count()));
@endphp

<aside id="sidebar" class="profile-article-sidebar" aria-label="Informasi profil desa">
    <section class="profile-side-widget leader-profile-card">
        <h2 class="profile-side-title">Profil Pimpinan</h2>
        <div class="leader-card-copy">
            <p class="leader-role">{{ $leader['role'] }}</p>
            <h3>{{ $leader['name'] }}</h3>
            <p class="leader-greeting">{{ $leader['greeting'] }}</p>
            <a class="leader-read-more" href="{{ route('pemerintahan-desa') }}">
                Baca Profil <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </section>

    <section class="profile-side-widget regulation-widget">
        <h2 class="profile-side-title">Peraturan Desa</h2>
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
    </section>

    <section class="profile-side-widget latest-comments-widget">
        <h2 class="profile-side-title">
            <span>Komentar Terbaru</span>
            <a class="profile-comments-link" href="{{ route('profile-desa.comments') }}" aria-label="Lihat seluruh komentar identitas desa" title="Lihat semua komentar">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </a>
        </h2>
        <div class="latest-comment-list">
            @foreach ($comments as $comment)
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
            @endforeach
        </div>
    </section>
</aside>
