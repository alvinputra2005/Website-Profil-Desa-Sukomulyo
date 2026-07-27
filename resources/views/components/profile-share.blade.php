@props(['url', 'text', 'label' => 'halaman profil desa'])

<aside class="article-share-panel" aria-label="Bagikan {{ $label }}">
    <button
        class="article-share-label"
        type="button"
        data-share-native
        data-share-url="{{ $url }}"
        data-share-text="{{ $text }}"
        aria-label="Bagikan {{ $label }}"
        title="Bagikan {{ $label }}"
    >
        <i class="fas fa-share-alt" aria-hidden="true"></i>
        <span class="screen-reader-text">Bagikan {{ $label }}</span>
    </button>
    <div class="article-share-actions">
        <a class="article-share-button" href="https://wa.me/?text={{ rawurlencode($text.' '.$url) }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke WhatsApp" title="WhatsApp">
            <i class="fab fa-whatsapp" aria-hidden="true"></i>
            <span class="screen-reader-text">WhatsApp</span>
        </a>
        <a class="article-share-button" href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($url) }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke Facebook" title="Facebook">
            <i class="fab fa-facebook-f" aria-hidden="true"></i>
            <span class="screen-reader-text">Facebook</span>
        </a>
        <a class="article-share-button" href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" data-share-instagram data-share-url="{{ $url }}" data-share-text="{{ $text }}" aria-label="Salin tautan dan buka Instagram" title="Instagram">
            <i class="fab fa-instagram" aria-hidden="true"></i>
            <span class="screen-reader-text">Salin tautan dan buka Instagram</span>
        </a>
    </div>
    <span class="article-share-status" data-share-status aria-live="polite"></span>
</aside>
