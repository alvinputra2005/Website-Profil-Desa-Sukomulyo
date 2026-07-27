<x-layouts.app
    title="Identitas Desa"
    :description="'Informasi lengkap identitas, wilayah, pemerintahan, dan karakter '.$site['name'].'.'"
>
    <x-page-header title="Identitas Desa" :breadcrumbs="[
        ['label' => 'Profile Desa'],
        ['label' => 'Identitas Desa'],
    ]" />

    @php
        $shareUrl = route('profile-desa');
        $shareText = 'Identitas Desa Sukomulyo - '.$site['name'];
        $villageRows = collect($identityGroups)->flatMap(fn ($group) => $group['rows'])->keyBy('label');
        $villageAddress = $villageRows->get('Alamat Kantor Desa')['value'] ?? $site['address'];
        $villageHead = $villageRows->get('Nama Kepala Desa')['value'] ?? $villageLeader['name'];
    @endphp

    <div class="container news-detail-container profile-detail-container">
        <div id="sc_innerpage_wrap" class="news-detail-layout profile-article-layout" data-hide-back-to-top data-disable-scroll-reveal>
            <article class="sc_innerpage_contentbx single-article village-profile-article">
                <header class="entry-header">
                    <h1 class="entry-title">Identitas Desa Sukomulyo</h1>
                    <div class="postmeta" aria-label="Informasi artikel">
                        <span class="post-date"><i class="far fa-calendar-alt" aria-hidden="true"></i>Diperbarui {{ now()->translatedFormat('d F Y') }}</span>
                        <span class="post-author"><i class="far fa-user" aria-hidden="true"></i>Pemerintah Desa Sukomulyo</span>
                        <button class="profile-print-button" type="button" data-print-article>
                            <i class="fas fa-print" aria-hidden="true"></i>Cetak Artikel
                        </button>
                    </div>
                </header>

                <figure class="news-detail-hero profile-detail-hero">
                    <img src="{{ asset('assets/village-rice-fields.jpg') }}" alt="Pemandangan wilayah Desa Sukomulyo">
                    <figcaption>Gambaran wilayah dan kehidupan masyarakat Desa Sukomulyo.</figcaption>
                </figure>

                <div class="article-reading-body">
                    <p class="article-lead">
                        Desa Sukomulyo merupakan desa yang tumbuh dengan semangat kebersamaan, pelayanan publik, dan gotong royong. Halaman ini menyajikan identitas resmi desa sebagai rujukan masyarakat dalam mengenal wilayah dan pemerintahan desa.
                    </p>

                    <div class="entry-content">
                        <h2>Gambaran Umum Desa</h2>
                        <p>
                            Desa Sukomulyo adalah kesatuan masyarakat hukum yang memiliki batas wilayah dan berwenang mengatur kepentingan masyarakat setempat berdasarkan prakarsa masyarakat serta ketentuan peraturan perundang-undangan. Pusat pelayanan pemerintahan desa beralamat di {{ $villageAddress ?: 'Kantor Desa Sukomulyo' }}.
                        </p>
                        <p>
                            Pemerintahan desa dipimpin oleh {{ $villageHead ?: 'Kepala Desa Sukomulyo' }} bersama perangkat desa. Pelayanan diarahkan agar warga memperoleh informasi, administrasi, dan pendampingan secara ramah, terbuka, serta dapat dipertanggungjawabkan.
                        </p>

                    </div>

                    <footer class="article-footer">
                        <strong>Tag:</strong>
                        <span>Identitas Desa</span>
                        <span>Profil Sukomulyo</span>
                        <span>Pelayanan Publik</span>
                    </footer>
                </div>

                <section id="komentar" class="profile-comment-section" aria-labelledby="comment-form-title">
                    <div class="profile-comment-heading">
                        <h2 id="comment-form-title">Tulis Komentar</h2>
                        <p>Berikan saran, koreksi data, atau tanggapan mengenai Identitas Desa Sukomulyo.</p>
                    </div>

                    @if (session('comment_success'))
                        <div class="profile-comment-alert profile-comment-alert--success" role="status">
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                            {{ session('comment_success') }}
                        </div>
                    @endif

                    @if ($errors->any())
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
            </article>

            <aside class="article-share-panel" aria-label="Bagikan Identitas Desa">
                <button
                    class="article-share-label"
                    type="button"
                    data-share-native
                    data-share-url="{{ $shareUrl }}"
                    data-share-text="{{ $shareText }}"
                    aria-label="Bagikan artikel"
                    title="Bagikan artikel"
                >
                    <i class="fas fa-share-alt" aria-hidden="true"></i>
                    <span class="screen-reader-text">Bagikan artikel</span>
                </button>
                <div class="article-share-actions">
                    <a class="article-share-button" href="https://wa.me/?text={{ rawurlencode($shareText.' '.$shareUrl) }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke WhatsApp" title="WhatsApp">
                        <i class="fab fa-whatsapp" aria-hidden="true"></i>
                        <span class="screen-reader-text">WhatsApp</span>
                    </a>
                    <a class="article-share-button" href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($shareUrl) }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke Facebook" title="Facebook">
                        <i class="fab fa-facebook-f" aria-hidden="true"></i>
                        <span class="screen-reader-text">Facebook</span>
                    </a>
                    <a class="article-share-button" href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" data-share-instagram data-share-url="{{ $shareUrl }}" data-share-text="{{ $shareText }}" aria-label="Salin tautan dan buka Instagram" title="Instagram">
                        <i class="fab fa-instagram" aria-hidden="true"></i>
                        <span class="screen-reader-text">Salin tautan dan buka Instagram</span>
                    </a>
                </div>
                <span class="article-share-status" data-share-status aria-live="polite"></span>
            </aside>

            <x-profile-sidebar :leader="$villageLeader" :regulations="$villageRegulations" :latest-comments="$latestComments" />
        </div>
    </div>
</x-layouts.app>
