@props(['title', 'description' => null, 'breadcrumbs' => null])

@php
    $trail = $breadcrumbs ?? [['label' => $title]];
@endphp

<header class="page-banner">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            @foreach ($trail as $crumb)
                @if (! $loop->first)<span aria-hidden="true">/</span>@endif
                @if (! empty($crumb['url']) && ! $loop->last)
                    <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                @else
                    <span @if($loop->last) aria-current="page" @endif>{{ $crumb['label'] }}</span>
                @endif
            @endforeach
        </nav>
        <h1>{{ $title }}</h1>
        @if ($description)
            <p>{{ $description }}</p>
        @endif
    </div>
</header>
