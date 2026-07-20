@props(['title', 'description' => null])

<header class="page-banner">
    <div class="container">
        <p class="breadcrumbs"><a href="{{ route('home') }}">Beranda</a><span>/</span>{{ $title }}</p>
        <h1>{{ $title }}</h1>
        @if ($description)
            <p>{{ $description }}</p>
        @endif
    </div>
</header>
