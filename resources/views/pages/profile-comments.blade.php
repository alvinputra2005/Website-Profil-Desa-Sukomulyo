<x-layouts.app title="Komentar Identitas Desa">
    <x-page-header title="Komentar Identitas Desa" :breadcrumbs="[
        ['label' => 'Profile Desa', 'url' => route('profile-desa')],
        ['label' => 'Identitas Desa', 'url' => route('profile-desa')],
        ['label' => 'Komentar Identitas Desa'],
    ]" />

    <div class="container">
        <div id="sc_innerpage_wrap" class="profile-comments-layout">
            <section class="sc_innerpage_contentbx fullwidth profile-comments-page" aria-labelledby="profile-comments-title">
                <header class="profile-comments-heading">
                    <h1 id="profile-comments-title">Komentar Identitas Desa</h1>
                    <p>Berikut tanggapan warga mengenai Identitas Desa Sukomulyo.</p>
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
                        <p class="profile-comments-empty">Belum ada komentar untuk Identitas Desa.</p>
                    @endforelse
                </div>

                @if ($comments->hasPages())
                    <div class="profile-comments-pagination">
                        {{ $comments->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
