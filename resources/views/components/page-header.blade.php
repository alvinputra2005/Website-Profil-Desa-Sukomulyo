@props([
    'title',
    'description' => null,
    'breadcrumbs' => null,
    'showBreadcrumbs' => true,
    'showDivider' => true,
])

@php
    $trail = $breadcrumbs ?? [['label' => $title]];
@endphp

<header @class([
    'page-banner',
    'page-banner--no-breadcrumbs' => ! $showBreadcrumbs,
    'page-banner--no-divider' => ! $showDivider,
])>
    <div class="container">
        @if ($showBreadcrumbs)
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a class="breadcrumb-home" href="{{ route('beranda') }}">
                    <i class="fas fa-home" aria-hidden="true"></i>
                    <span>Beranda</span>
                </a>
                @foreach ($trail as $crumb)
                    <i class="breadcrumb-separator fas fa-chevron-right" aria-hidden="true"></i>
                    @if (! empty($crumb['url']) && ! $loop->last)
                        <a class="breadcrumb-link" href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                    @else
                        <span class="breadcrumb-current" @if($loop->last) aria-current="page" @endif>{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            </nav>
        @endif
        <div class="page-banner-heading">
            <h1>{{ $title }}</h1>
            @if ($description)
                <p>{{ $description }}</p>
            @endif
        </div>
    </div>
</header>
