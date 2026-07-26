<x-layouts.app :title="$page['title']">
    <x-page-header :title="$page['title']" :description="$page['description']" :breadcrumbs="[
        ['label' => 'Informasi Desa', 'url' => route('informasi-publik-desa')],
        ['label' => $page['title']],
    ]" />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth">
                <div class="data-section-grid">
                    @foreach ($items as $item)
                        <article class="data-panel">
                            @if (!empty($item['date']))
                                <span class="section-kicker">{{ $item['date'] }}</span>
                            @endif
                            <h2>{{ $item['title'] }}</h2>
                            @if (!empty($item['excerpt']))
                                <p>{{ $item['excerpt'] }}</p>
                            @endif
                            @if (!empty($item['html']))
                                <div>{!! $item['content'] !!}</div>
                            @else
                                <p>{{ $item['content'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
