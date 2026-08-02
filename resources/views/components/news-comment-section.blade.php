@props(['slug', 'commentKey', 'comments' => collect(), 'count' => 0])

<section id="komentar" class="profile-comment-section news-comment-section" aria-labelledby="news-comment-title">
    <div class="profile-comment-heading">
        <h2 id="news-comment-title">Komentar Berita</h2>
        <p>{{ number_format($count, 0, ',', '.') }} komentar warga. Sampaikan tanggapan Anda mengenai berita ini.</p>
    </div>
    @if (session('comment_success'))
        <div class="profile-comment-alert profile-comment-alert--success" role="status">{{ session('comment_success') }}</div>
    @endif
    @if ($errors->any())
        <div class="profile-comment-alert profile-comment-alert--error" role="alert">
            <strong>Komentar belum dapat dikirim.</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @if ($comments->isNotEmpty())
        <div class="profile-comments-list news-comments-list">
            @foreach ($comments as $comment)
                <article class="profile-comment-card">
                    <span class="profile-comment-card-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($comment->name, 0, 1)) }}</span>
                    <div class="profile-comment-card-content">
                        <div class="profile-comment-card-meta"><h3>{{ $comment->name }}</h3><time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->diffForHumans() }}</time></div>
                        <p>{{ $comment->comment }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
    <form class="profile-comment-form" action="{{ route('berita-desa.comment', $slug) }}" method="POST">
        @csrf
        <div class="profile-honeypot" aria-hidden="true"><label for="news-comment-website">Website</label><input id="news-comment-website" type="text" name="website" value="" tabindex="-1" autocomplete="off"></div>
        <div class="profile-form-field"><label for="news-comment-body">Komentar <span aria-hidden="true">*</span></label><textarea id="news-comment-body" name="comment" rows="5" maxlength="1500" required placeholder="Tuliskan tanggapan Anda...">{{ old('comment') }}</textarea></div>
        <div class="profile-comment-fields">
            <div class="profile-form-field"><label for="news-comment-name">Nama <span aria-hidden="true">*</span></label><input id="news-comment-name" type="text" name="name" value="{{ old('name') }}" maxlength="100" required></div>
            <div class="profile-form-field"><label for="news-comment-phone">Nomor HP <span aria-hidden="true">*</span></label><input id="news-comment-phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="25" required></div>
        </div>
        <div class="profile-form-field"><label for="news-comment-address">Alamat <span aria-hidden="true">*</span></label><input id="news-comment-address" type="text" name="address" value="{{ old('address') }}" maxlength="300" required></div>
        <p class="profile-comment-privacy"><i class="fas fa-lock" aria-hidden="true"></i> Nomor HP dan alamat tidak ditampilkan kepada publik.</p>
        <button class="profile-comment-submit" type="submit"><i class="fas fa-paper-plane" aria-hidden="true"></i>Kirim Komentar</button>
    </form>
</section>
