@props([
    'leader',
    'regulations' => [],
    'latestComments' => [],
])

<aside id="sidebar" class="profile-article-sidebar" aria-label="Informasi profil desa">
    <section class="profile-side-widget profile-links-widget">
        <h2 class="profile-side-title">Profil Desa</h2>
        <nav aria-label="Menu profil desa">
            <a class="is-active" href="{{ route('profile-desa') }}">Identitas Desa</a>
            <a href="{{ route('profile-desa.detail', 'sejarah') }}">Sejarah Desa</a>
            <a href="{{ route('profile-desa.detail', 'visi-misi') }}">Visi dan Misi</a>
            <a href="{{ route('pemerintahan-desa') }}">Struktur Pemerintahan</a>
            <a href="{{ route('potensi-desa') }}">Potensi Desa</a>
        </nav>
    </section>

    <section class="profile-side-widget leader-profile-card">
        <h2 class="profile-side-title">Profil Pimpinan</h2>
        <div class="leader-photo">
            @if ($leader['photo'])
                <img src="{{ $leader['photo'] }}" alt="{{ $leader['photo_alt'] }}">
            @else
                <div class="leader-photo-placeholder" role="img" aria-label="{{ $leader['photo_alt'] }}">
                    <i class="fas fa-user-tie" aria-hidden="true"></i>
                    <img src="{{ asset('assets/logo-brighter-sukomulyo.svg') }}" alt="">
                </div>
            @endif
        </div>
        <p class="leader-role">{{ $leader['role'] }}</p>
        <h3>{{ $leader['name'] }}</h3>
        <p class="leader-greeting">{{ $leader['greeting'] }}</p>
        <a class="leader-read-more" href="{{ route('pemerintahan-desa') }}">
            Baca Profil <i class="fas fa-arrow-right" aria-hidden="true"></i>
        </a>
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
                    <span class="regulation-cover" aria-hidden="true">
                        <span class="regulation-emblem"><i class="fas fa-landmark"></i></span>
                        <span class="regulation-cover-title">Peraturan<br>Desa</span>
                        <span class="regulation-cover-lines"></span>
                        <span class="regulation-pdf-badge"><i class="fas fa-file-pdf"></i> PDF</span>
                    </span>
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
        <h2 class="profile-side-title">Komentar Terbaru</h2>
        <div class="latest-comment-list">
            @forelse ($latestComments as $comment)
                <article class="latest-comment">
                    <span class="latest-comment-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($comment->name, 0, 1)) }}</span>
                    <div>
                        <h3>{{ $comment->name }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($comment->comment, 86) }}</p>
                        <time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->diffForHumans() }}</time>
                    </div>
                </article>
            @empty
                <p class="latest-comment-empty">Belum ada komentar. Jadilah warga pertama yang memberikan tanggapan.</p>
            @endforelse
        </div>
    </section>
</aside>
