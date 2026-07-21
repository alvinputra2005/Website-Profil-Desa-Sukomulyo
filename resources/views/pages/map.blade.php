<x-layouts.app title="Peta Desa">
    <x-page-header title="Peta Desa" description="Temukan lokasi dan informasi wilayah Desa Sukomulyo." />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <section class="sc_innerpage_contentbx fullwidth">
                <div class="map-layout">
                    <div class="map-frame">
                        <iframe
                            src="https://www.google.com/maps?q=Desa%20Sukomulyo&output=embed"
                            title="Peta lokasi Desa Sukomulyo"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen>
                        </iframe>
                    </div>

                    <aside class="map-information">
                        <span class="section-kicker">Lokasi Desa</span>
                        <h2>{{ $site['name'] }}</h2>
                        <p>Peta membantu masyarakat menemukan kantor desa dan mengenali posisi wilayah Desa Sukomulyo.</p>
                        <ul>
                            <li><i class="fas fa-map-marker-alt" aria-hidden="true"></i><span><strong>Alamat Kantor Desa</strong>{{ $site['address'] }}</span></li>
                            <li><i class="fas fa-phone" aria-hidden="true"></i><span><strong>Telepon</strong>{{ $site['phone'] }}</span></li>
                            <li><i class="fas fa-envelope" aria-hidden="true"></i><span><strong>Email</strong>{{ $site['email'] }}</span></li>
                        </ul>
                        <a class="learnmore" href="https://www.google.com/maps/search/?api=1&query=Desa+Sukomulyo" target="_blank" rel="noopener noreferrer">Buka di Google Maps</a>
                    </aside>
                </div>

                <p class="data-note"><i class="fas fa-info-circle" aria-hidden="true"></i> Titik koordinat dapat diperbarui setelah koordinat resmi kantor Desa Sukomulyo tersedia.</p>
            </section>
        </div>
    </div>
</x-layouts.app>
