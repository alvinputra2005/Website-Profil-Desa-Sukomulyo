<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="{{ request()->routeIs('admin.inventory.report*') ? 'active' : '' }}"><a href="{{ route('admin.inventory.report') }}"><i class="fa fa-bar-chart"></i> Laporan</a></li>
        @foreach(\App\Support\InventoryCategory::all() as $slug => $data)
            <li class="{{ ($category ?? null) === $slug ? 'active' : '' }}"><a href="{{ route('admin.inventory.index', $slug) }}"><i class="fa {{ $data['icon'] }}"></i> {{ $data['label'] }}</a></li>
        @endforeach
    </ul>
</div>
