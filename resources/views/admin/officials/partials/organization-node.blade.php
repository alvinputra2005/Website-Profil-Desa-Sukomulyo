<div class="organization-branch">
    <article class="organization-card" style="border-top-color:{{ $node['official']->organization_color ?: '#526b42' }}; transform:translateX({{ $node['official']->organization_offset ?? 0 }}%);">
        @if($node['official']->photo)
            <img src="{{ $node['official']->photo->thumbnail_url }}" alt="{{ $node['official']->photo->alt_text ?: $node['official']->name }}">
        @else
            <span>{{ strtoupper(substr($node['official']->name, 0, 1)) }}</span>
        @endif
        <div>
            <strong>{{ $node['official']->full_name }}</strong>
            <small>{{ $node['official']->position_label }}</small>
        </div>
    </article>
    @if($node['children'])
        <div class="organization-children {{ $node['official']->organization_layout === 'hanging' ? 'is-hanging' : '' }}">
            @foreach($node['children'] as $child)
                @include('admin.officials.partials.organization-node', ['node' => $child])
            @endforeach
        </div>
    @endif
</div>
