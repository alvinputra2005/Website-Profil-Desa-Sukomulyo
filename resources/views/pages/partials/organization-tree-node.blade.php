@php
    $official = $node['official'];
    $photo = $official->photo;
    $assetPhotos = [
        'safiul anwar, st' => '/assets/safiul-anwar.jpeg',
        'angga saputra' => '/assets/angga-saputra.jpeg',
        'wike priharti y' => '/assets/wike-priharti-y.jpeg',
        'mohamad sholeh' => '/assets/muhammad-sholeh.jpeg',
        'suwarno' => '/assets/suwarno.jpeg',
        'reza tri purnomo' => '/assets/reza-tri.jpeg',
        'catur yulianto' => '/assets/catur-yulianto.jpeg',
        'bambang s' => '/assets/bambang.jpeg',
        'sispanaji' => '/assets/sispanaji.jpeg',
        'nikita f z' => '/assets/nikita.jpeg',
        'fendi priyo s' => '/assets/fendi-priyo.jpeg',
    ];
    $photoUrl = $photo?->thumbnail_url
        ?: ($assetPhotos[mb_strtolower(trim($official->full_name))] ?? null);
    $photoAlt = $photo?->alt_text
        ?: $official->full_name.' - '.$official->position_label;
    $initials = collect(preg_split('/[\s,]+/u', trim($official->full_name)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $position = mb_strtolower(trim($official->position));
    $alignWithKaur = str_starts_with($position, 'kasi')
        || str_starts_with($position, 'kasun')
        || str_starts_with($position, 'kepala dusun');
@endphp

<div class="public-organization-branch {{ $alignWithKaur ? 'public-organization-branch--align-with-kaur' : '' }}">
    <button
        type="button"
        class="public-organization-card"
        data-org-node
        aria-label="{{ $official->full_name }}, {{ $official->position_label }}. Tekan untuk memilih perangkat desa."
        aria-pressed="false"
        style="--organization-color: {{ $official->organization_color ?: '#526b42' }}; --organization-offset: {{ (int) ($official->organization_offset ?? 0) }}%;"
    >
        <span class="public-organization-card__portrait {{ $photoUrl ? 'has-photo' : '' }}" data-org-portrait>
            <span class="public-organization-card__initial" aria-hidden="true">{{ $initials ?: '?' }}</span>

            @if ($photoUrl)
                <img
                    src="{{ $photoUrl }}"
                    alt="{{ $photoAlt }}"
                    loading="{{ $official->superior_id ? 'lazy' : 'eager' }}"
                    decoding="async"
                    @if (! $official->superior_id) fetchpriority="high" @endif
                    data-org-image
                >
            @endif
        </span>

        <span class="public-organization-card__copy">
            <strong>{{ $official->full_name }}</strong>
            <small>{{ $official->position_label }}</small>
        </span>
    </button>

    @if ($node['children'])
        <div class="public-organization-children {{ $official->organization_layout === 'hanging' ? 'is-hanging' : '' }}">
            @foreach ($node['children'] as $child)
                @include('pages.partials.organization-tree-node', ['node' => $child])
            @endforeach
        </div>
    @endif
</div>
