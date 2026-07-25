<x-layouts.app :title="$page['title']">
    <x-page-header :title="$page['title']" :description="$page['description']" :breadcrumbs="[
        ['label' => 'Profile Desa', 'url' => route('profile-desa')],
        ['label' => $page['title']],
    ]" />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth">
                <div class="data-section-grid">
                    @forelse ($sections as $section)
                        <article class="data-panel">
                            <h2>{{ $section->title }}</h2>
                            @if ($section->image)
                                <img class="profile-section-image" src="{{ $section->image->url }}" alt="{{ $section->image->alt_text ?: $section->title }}">
                            @endif
                            <div>{!! $section->content !!}</div>
                        </article>
                    @empty
                        @foreach ($page['fallback'] as $section)
                            <article class="data-panel">
                                <h2>{{ $section['title'] }}</h2>
                                <p>{{ $section['content'] }}</p>
                            </article>
                        @endforeach
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
