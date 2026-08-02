<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#526b42">
    <title>@yield('title') | Desa Sukomulyo</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/logo-brighter-sukomulyo.svg') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/bootstrap/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/login-style.css') }}">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="auth-visual" aria-labelledby="welcome-title">
            <div class="auth-visual-pattern" aria-hidden="true"></div>

            <a class="auth-brand" href="{{ route('beranda') }}" aria-label="Kembali ke beranda Desa Sukomulyo">
                <span class="auth-brand-logo">
                    <img src="{{ asset('assets/logo-brighter-sukomulyo.svg') }}" alt="">
                </span>
                <span class="auth-brand-copy">
                    <span class="auth-brand-kicker">Website Resmi</span>
                    <strong>Desa Sukomulyo</strong>
                    <small>Portal Informasi dan Pelayanan Desa</small>
                </span>
            </a>

            <div class="auth-welcome">
                <h1 id="welcome-title">
                    Selamat Datang
                    <span>di Panel Admin</span>
                </h1>
                <p>Kelola informasi desa, berita, galeri, data kependudukan, dan komentar warga dari satu panel terintegrasi.</p>
            </div>
        </section>

        <section class="auth-panel" aria-labelledby="auth-heading">
            <div class="auth-panel-inner">
                <a class="auth-mobile-brand" href="{{ route('beranda') }}" aria-label="Kembali ke beranda Desa Sukomulyo">
                    <img src="{{ asset('assets/logo-brighter-sukomulyo.svg') }}" alt="">
                    <span><strong>Desa Sukomulyo</strong><small>Panel Administrasi</small></span>
                </a>

                <header class="auth-header">
                    <h2 id="auth-heading">@yield('heading')</h2>
                    <p>@yield('description')</p>
                </header>

                @if(session('status'))
                    <div class="auth-alert auth-alert-info" role="status">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if(session('success'))
                    <div class="auth-alert auth-alert-success" role="status">
                        <i class="fa fa-check-circle" aria-hidden="true"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </section>
    </main>

    @stack('scripts')
</body>
</html>
