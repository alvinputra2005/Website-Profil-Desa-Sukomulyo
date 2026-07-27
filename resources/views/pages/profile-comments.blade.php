<x-layouts.app :title="'Komentar '.$page['title']">
    <x-page-header :title="'Komentar '.$page['title']" :breadcrumbs="[
        ['label' => 'Profile Desa', 'url' => route('profile-desa')],
        ['label' => $page['title'], 'url' => $page['url']],
        ['label' => 'Komentar'],
    ]" />

    <div class="container">
        <div id="sc_innerpage_wrap" class="profile-comments-layout">
            <section class="sc_innerpage_contentbx fullwidth profile-comments-page" aria-labelledby="profile-comments-title">
                <header class="profile-comments-heading">
                    <h1 id="profile-comments-title">Komentar {{ $page['title'] }}</h1>
                    <p>{{ number_format($comments->total(), 0, ',', '.') }} tanggapan warga mengenai {{ $page['title'] }}.</p>
                </header>

                <div class="profile-comments-list">
                    @forelse ($comments as $comment)
                        <article id="komentar-{{ $comment->id }}" class="profile-comment-card">
                            <span class="profile-comment-card-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($comment->name, 0, 1)) }}</span>
                            <div class="profile-comment-card-content">
                                <header>
                                    <h2>{{ $comment->name }}</h2>
                                    <time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->diffForHumans() }}</time>
                                </header>
                                <p>{{ $comment->comment }}</p>
                                <form action="{{ route('profile-desa.comments.like', $comment) }}" method="POST">
                                    @csrf
                                    <button class="profile-comment-like-button" type="submit" aria-label="Sukai komentar {{ $comment->name }}">
                                        <i class="far fa-thumbs-up" aria-hidden="true"></i>
                                        <span>Suka</span>
                                        <strong>{{ $comment->like_count }}</strong>
                                    </button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <p class="profile-comments-empty">Belum ada komentar untuk {{ $page['title'] }}.</p>
                    @endforelse
                </div>

                @if ($comments->hasPages())
                    <div class="profile-comments-pagination">
                        {{ $comments->links() }}
                    </div>
                @endif

                <a class="profile-comments-back" href="{{ $page['url'] }}#komentar">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>Kembali ke {{ $page['title'] }}
                </a>
            </section>
        </div>
    </div>
</x-layouts.app>
