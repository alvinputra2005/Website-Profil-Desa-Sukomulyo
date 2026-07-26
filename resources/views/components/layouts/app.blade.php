@props([
    'title' => null,
    'description' => null,
    'keywords' => null,
    'canonical' => null,
    'robots' => 'index, follow',
    'ogImage' => null,
    'ogType' => 'website',
    'structuredData' => null,
])
@php
    $metaTitle = $title ? $title.' | '.$site['name'] : $site['name'];
    $metaDescription = $description ?: $site['tagline'];
    $canonicalUrl = $canonical ?: request()->url();
    $socialImage = $ogImage ?: asset('assets/village-rice-fields.jpg');
    $socialImage = preg_match('~^https?://~i', $socialImage) ? $socialImage : url($socialImage);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    @if($keywords)<meta name="keywords" content="{{ $keywords }}">@endif
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ $socialImage }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $socialImage }}">
    @if($structuredData)
        <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
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
