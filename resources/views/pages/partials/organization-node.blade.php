@php
    $size = $size ?? 'default';
    $delay = $delay ?? 0;
    $hasPhoto = !empty($official['photo']);
@endphp

@if ($official)
    <button
        type="button"
        class="org-node org-node--{{ $size }}"
        data-org-node
        aria-label="{{ $official['name'] }}, {{ $official['role'] }}. Tekan untuk menampilkan atau menyembunyikan nama."
        aria-pressed="false"
        style="--org-node-delay: {{ (int) $delay }}ms"
    >
        <span class="org-node__portrait {{ $hasPhoto ? 'has-photo' : '' }}" data-org-portrait>
            <span class="org-node__fallback" aria-hidden="true">
                {{ $official['initials'] ?: '?' }}
            </span>

            @if ($hasPhoto)
                <img
                    src="{{ $official['photo'] }}"
                    alt="{{ $official['photo_alt'] ?: $official['name'].' - '.$official['role'] }}"
                    loading="{{ $size === 'leader' ? 'eager' : 'lazy' }}"
                    decoding="async"
                    @if ($size === 'leader') fetchpriority="high" @endif
                    data-org-image
                >
            @endif
        </span>

        <span class="org-node__role">{{ $official['role'] }}</span>
        <span class="org-node__name" aria-hidden="true">{{ $official['name'] }}</span>
    </button>
@endif
