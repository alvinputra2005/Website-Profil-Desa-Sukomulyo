@props(['site', 'navigation', 'articles'])

<header class="site-header siteinner">
    <div class="header-top">
        <div class="container topbar-content">
            <div class="news-ticker" aria-label="Berita terbaru">
                <span class="news-label">Berita</span>
                <div class="ticker-window">
                    <div class="ticker-track">
                        @foreach ($articles as $article)
                            <a href="{{ route('news.show', $article['slug']) }}">{{ $article['title'] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="header-socialicons" aria-label="Media sosial">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                <a href="#" aria-label="YouTube"><i class="fab fa-youtube" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>

    <div class="container brand-row">
        <div class="logo">
            <a class="brand-mark" href="{{ route('home') }}" aria-label="{{ $site['name'] }}">
                <i class="fas fa-landmark" aria-hidden="true"></i>
            </a>
            <div>
                <p class="eyebrow">Website Resmi</p>
                <h1><a href="{{ route('home') }}">{{ $site['name'] }}</a></h1>
                <p>{{ $site['tagline'] }}</p>
            </div>
        </div>

        <div class="header_right">
            <div class="infobox">
                <i class="fas fa-envelope" aria-hidden="true"></i>
                <span><span class="statictext">Email</span><a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></span>
            </div>
            <div class="infobox left-right-border">
                <i class="fas fa-phone" aria-hidden="true"></i>
                <span><span class="statictext">Telepon</span>{{ $site['phone'] }}</span>
            </div>
            <a class="donatenow service-button" href="{{ route('contact.index') }}">Layanan Desa</a>
        </div>
    </div>
</header>

<nav class="header-navigation" aria-label="Navigasi utama">
    <div class="container nav-inner">
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu">
            <span>Menu</span><i class="fas fa-bars" aria-hidden="true"></i>
        </button>
        <div class="mainhdrnav" id="primary-menu">
            <ul>
                @foreach ($navigation as $item)
                    <li class="{{ request()->routeIs($item['active']) ? 'current-menu-item' : '' }}">
                        <a href="{{ route($item['route']) }}" @if(request()->routeIs($item['active'])) aria-current="page" @endif>
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>
