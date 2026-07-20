@props(['site', 'navigation', 'categories'])

<footer class="footer-wrapper">
    <div class="container footer-grid">
        <section class="footer-widget footer-about">
            <h5>{{ $site['name'] }}</h5>
            <p>{{ $site['tagline'] }}. Menyediakan informasi desa yang terbuka, mudah dijangkau, dan bermanfaat bagi masyarakat.</p>
        </section>

        <section class="footer-widget">
            <h5>Tautan</h5>
            <ul>
                @foreach ($navigation as $item)
                    <li><a href="{{ route($item['route']) }}">{{ $item['label'] }}</a></li>
                @endforeach
            </ul>
        </section>

        <section class="footer-widget">
            <h5>Kategori Berita</h5>
            <ul>
                @foreach ($categories as $category)
                    <li><a href="{{ route('news.category', $category['category_slug']) }}">{{ $category['category'] }}</a></li>
                @endforeach
            </ul>
        </section>

        <section class="footer-widget footer-contact">
            <h5>Hubungi Kami</h5>
            <p><i class="fas fa-map-marker-alt" aria-hidden="true"></i>{{ $site['address'] }}</p>
            <p><i class="fas fa-phone" aria-hidden="true"></i>{{ $site['phone'] }}</p>
            <p><i class="fas fa-envelope" aria-hidden="true"></i><a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></p>
        </section>
    </div>

    <div class="footer-copyright">
        <div class="container copyright-row">
            <span>&copy; {{ date('Y') }} {{ $site['name'] }}.</span>
            <span>Konversi antarmuka Social Care Lite untuk Laravel.</span>
        </div>
    </div>
</footer>
