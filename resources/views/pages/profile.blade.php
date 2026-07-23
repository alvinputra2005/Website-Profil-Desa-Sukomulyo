<x-layouts.app title="Profil Desa">
    <x-page-header title="Profil Desa" description="Mengenal sejarah, visi, misi, dan identitas Desa Sukomulyo." />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <article class="sc_innerpage_contentbx profile-content">
                @if($profileSections->isNotEmpty())
                    @foreach($profileSections as $section)
                        <section>
                            <h2>{{ $section->title }}</h2>
                            @if($section->image)
                                <img class="profile-section-image" src="{{ $section->image->url }}" alt="{{ $section->image->alt_text ?: $section->title }}">
                            @endif
                            <div>{!! $section->content !!}</div>
                        </section>
                    @endforeach
                @else
                <section>
                    <h2>Sejarah Singkat</h2>
                    <p>Desa Sukomulyo berkembang dari kehidupan masyarakat yang menjunjung kebersamaan, kerja keras, dan gotong royong. Nilai tersebut menjadi dasar dalam setiap kegiatan sosial maupun pembangunan desa.</p>
                    <p>Informasi sejarah pada halaman ini disiapkan sebagai struktur awal dan dapat disesuaikan dengan data resmi serta cerita para tokoh masyarakat.</p>
                </section>

                <section class="vision-box">
                    <span class="section-kicker">Visi</span>
                    <blockquote>Terwujudnya Desa Sukomulyo yang maju, mandiri, sejahtera, dan berkarakter melalui tata kelola pemerintahan yang melayani.</blockquote>
                </section>

                <section>
                    <h2>Misi Desa</h2>
                    <ol class="mission-list">
                        <li>Meningkatkan kualitas pelayanan publik yang cepat, terbuka, dan bertanggung jawab.</li>
                        <li>Mendorong pembangunan desa berdasarkan kebutuhan serta partisipasi masyarakat.</li>
                        <li>Mengembangkan potensi pertanian, UMKM, sosial, seni, dan budaya lokal.</li>
                        <li>Meningkatkan kualitas lingkungan, kesehatan, pendidikan, dan kesejahteraan warga.</li>
                    </ol>
                </section>

                <section>
                    <h2>Identitas Desa</h2>
                    <div class="info-table-wrap">
                        <table class="info-table">
                            <tbody>
                                <tr><th>Nama Desa</th><td>Sukomulyo</td></tr>
                                <tr><th>Status</th><td>Pemerintahan Desa</td></tr>
                                <tr><th>Alamat Kantor</th><td>{{ $site['address'] }}</td></tr>
                                <tr><th>Email</th><td>{{ $site['email'] }}</td></tr>
                                <tr><th>Telepon</th><td>{{ $site['phone'] }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
                @endif
            </article>

            <x-sidebar :categories="$categories" :archive-years="$archiveYears" />
            <div class="clear"></div>
        </div>
    </div>
</x-layouts.app>
