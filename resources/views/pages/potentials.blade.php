<x-layouts.app title="Potensi Desa">
    <x-page-header title="Potensi Desa" description="Sumber daya dan kekuatan lokal yang mendukung kemajuan masyarakat." />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth">
                <div class="potential-grid">
                    @foreach ($potentials as $potential)
                        <article class="potential-card">
                            <div class="potential-image">
                                <img src="{{ $potential['image'] }}" alt="{{ $potential['title'] }}">
                                <span><i class="{{ $potential['icon'] }}" aria-hidden="true"></i></span>
                            </div>
                            <div class="potential-body">
                                <h2>{{ $potential['title'] }}</h2>
                                <p>{{ $potential['description'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="callout">
                    <div>
                        <h2>Punya produk atau potensi yang ingin ditampilkan?</h2>
                        <p>Sampaikan informasi kepada pemerintah desa agar dapat diverifikasi dan dipublikasikan.</p>
                    </div>
                    <a class="learnmore" href="{{ route('contact.index') }}">Hubungi Kami</a>
                </aside>
            </section>
        </div>
    </div>
</x-layouts.app>
