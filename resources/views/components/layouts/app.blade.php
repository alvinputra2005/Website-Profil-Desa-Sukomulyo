@props(['title' => null, 'description' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description ?? $site['tagline'] }}">
    <title>{{ $title ? $title.' | '.$site['name'] : $site['name'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Assistant:wght@300;400;600;700&family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/social-care-lite/fontsawesome/css/fontawesome-all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/social-care-lite/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/social-care-lite/responsive.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link screen-reader-text" href="#main-content">Lewati ke konten utama</a>

    <div id="sitelayout_type">
        <x-navbar :site="$site" :navigation="$navigation" :articles="$articles" />

        <main id="main-content">
            {{ $slot }}
        </main>

        <x-footer :site="$site" :navigation="$navigation" :categories="$categories" />
    </div>

    <button class="back-to-top" type="button" aria-label="Kembali ke atas" hidden>
        <i class="fas fa-chevron-up" aria-hidden="true"></i>
    </button>
</body>
</html>
