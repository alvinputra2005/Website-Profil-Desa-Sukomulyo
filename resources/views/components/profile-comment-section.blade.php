@props(['context'])

<section id="komentar" class="profile-comment-section" aria-labelledby="comment-form-title">
    <div class="profile-comment-heading">
        <h2 id="comment-form-title">Tulis Komentar</h2>
        <p>Berikan saran, koreksi data, atau tanggapan mengenai {{ $context['title'] }}.</p>
    </div>

    @if (session('comment_success'))
        <div class="profile-comment-alert profile-comment-alert--success" role="status">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            {{ session('comment_success') }}
        </div>
    @endif

    @if ($errors->any() && old('page_key', $context['page_key']) === $context['page_key'])
        <div class="profile-comment-alert profile-comment-alert--error" role="alert">
            <strong>Komentar belum dapat dikirim.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="profile-comment-form" action="{{ route('profile-desa.comment') }}" method="POST">
        @csrf
        <input type="hidden" name="page_key" value="{{ $context['page_key'] }}">
        <div class="profile-honeypot" aria-hidden="true">
            <label for="comment-website">Website</label>
            <input id="comment-website" type="text" name="website" value="" tabindex="-1" autocomplete="off">
        </div>
        <div class="profile-form-field">
            <label for="comment-body">Komentar <span aria-hidden="true">*</span></label>
            <textarea id="comment-body" name="comment" rows="6" maxlength="1500" required placeholder="Tuliskan komentar, saran, atau koreksi data desa...">{{ old('comment') }}</textarea>
        </div>
        <div class="profile-comment-fields">
            <div class="profile-form-field">
                <label for="comment-name">Nama <span aria-hidden="true">*</span></label>
                <input id="comment-name" type="text" name="name" value="{{ old('name') }}" maxlength="100" autocomplete="name" required placeholder="Nama lengkap">
            </div>
            <div class="profile-form-field">
                <label for="comment-phone">Nomor HP <span aria-hidden="true">*</span></label>
                <input id="comment-phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="25" autocomplete="tel" inputmode="tel" required placeholder="08xxxxxxxxxx">
            </div>
        </div>
        <div class="profile-form-field">
            <label for="comment-address">Alamat <span aria-hidden="true">*</span></label>
            <input id="comment-address" type="text" name="address" value="{{ old('address') }}" maxlength="300" autocomplete="street-address" required placeholder="Dusun/RT/RW atau alamat domisili">
        </div>
        <p class="profile-comment-privacy"><i class="fas fa-lock" aria-hidden="true"></i> Nomor HP dan alamat hanya digunakan untuk verifikasi dan tidak ditampilkan kepada publik.</p>
        <button class="profile-comment-submit" type="submit">
            <i class="fas fa-paper-plane" aria-hidden="true"></i>Kirim Komentar
        </button>
    </form>
</section>
