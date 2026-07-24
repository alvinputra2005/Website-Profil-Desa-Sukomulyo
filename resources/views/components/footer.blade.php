@props(['site', 'navigation', 'categories'])

<footer class="footer-wrapper">
    <div class="container footer-grid">
        <section class="footer-widget footer-about">
            <div class="footer-brand">
                <h5>{{ $site['name'] }}</h5>
            </div>
            <p>{{ $site['tagline'] }} untuk menghadirkan informasi desa yang terbuka dan mudah dijangkau masyarakat.</p>
            <div class="footer-contact">
                <p><i class="fas fa-map-marker-alt" aria-hidden="true"></i>{{ $site['address'] }}</p>
                <p><i class="fas fa-phone" aria-hidden="true"></i>{{ $site['phone'] }}</p>
                <p><i class="fas fa-envelope" aria-hidden="true"></i><a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></p>
            </div>
        </section>

        <section class="footer-widget">
            <h5>Navigasi</h5>
            <ul>
                @foreach ($navigation as $item)
                    <li><a href="{{ route($item['route']) }}">{{ $item['label'] }}</a></li>
                @endforeach
            </ul>
        </section>

        <section class="footer-widget">
            <h5>Media Sosial</h5>
            <div class="footer-social-links" aria-label="Media sosial Desa Sukomulyo">
                <a href="#" aria-label="Facebook Desa Sukomulyo"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                <a href="#" aria-label="Instagram Desa Sukomulyo"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                <a href="#" aria-label="YouTube Desa Sukomulyo"><i class="fab fa-youtube" aria-hidden="true"></i></a>
            </div>
        </section>

        <section class="footer-widget footer-collaboration">
            <h5>Kolaborasi</h5>
            <div class="footer-partner-logos">
                <img class="footer-village-logo" src="{{ asset('assets/logo-brighter-sukomulyo.jpeg') }}" alt="Logo Brighter Sukomulyo">
                <img src="{{ asset('assets/logo-universitas-negeri-malang.webp') }}" alt="Logo Universitas Negeri Malang">
                <img class="footer-kkn-logo" src="{{ asset('assets/logo-kkn-sukomulyo.png') }}" alt="Logo KKN Sukomulyo">
            </div>
            <p>Dikembangkan bersama oleh Tim KKN Desa Sukomulyo Universitas Negeri Malang 2026.</p>
        </section>
    </div>

    <div class="footer-copyright">
        <div class="container copyright-row">
            <span>&copy; {{ date('Y') }} Pemerintah {{ $site['name'] }}.</span>
            <span>Bersama KKN Universitas Negeri Malang 2026.</span>
        </div>
    </div>
</footer>
