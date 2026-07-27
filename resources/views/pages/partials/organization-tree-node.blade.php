@php
    $official = $node['official'];
    $photo = $official->photo;
    $initials = collect(preg_split('/[\s,]+/u', trim($official->full_name)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<div class="public-organization-branch">
    <article
        class="public-organization-card"
        style="--organization-color: {{ $official->organization_color ?: '#526b42' }}; --organization-offset: {{ (int) ($official->organization_offset ?? 0) }}%;"
    >
        @if ($photo)
            <img
                src="{{ $photo->thumbnail_url }}"
                alt="{{ $photo->alt_text ?: $official->full_name }}"
                loading="lazy"
                decoding="async"
            >
        @else
            <span class="public-organization-card__initial" aria-hidden="true">{{ $initials ?: '?' }}</span>
        @endif

        <span>
            <strong>{{ $official->full_name }}</strong>
            <small>{{ $official->position_label }}</small>
        </span>
    </article>

    @if ($node['children'])
        <div class="public-organization-children {{ $official->organization_layout === 'hanging' ? 'is-hanging' : '' }}">
            @foreach ($node['children'] as $child)
                @include('pages.partials.organization-tree-node', ['node' => $child])
            @endforeach
        </div>
    @endif
</div>
