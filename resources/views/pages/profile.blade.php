<x-layouts.app title="Profil Desa">
    <x-page-header title="Profil Desa" :description="'Mengenal sejarah, visi, misi, dan identitas '.$site['name'].'.'" />

    <div class="container">
        <div id="sc_innerpage_wrap">
            <article class="sc_innerpage_contentbx profile-content">
                <section class="village-identity">
                    <h2>Identitas Desa</h2>
                    <div class="info-table-wrap">
                        <table class="info-table identity-table">
                            <tbody>
                                @foreach($identityGroups as $group)
                                    <tr class="identity-group">
                                        <th colspan="2">{{ $group['title'] }}</th>
                                    </tr>
                                    @foreach($group['rows'] as $row)
                                        <tr>
                                            <th>{{ $row['label'] }}</th>
                                            <td>
                                                @if(($row['type'] ?? null) === 'email' && $row['value'])
                                                    <a href="mailto:{{ $row['value'] }}">{{ $row['value'] }}</a>
                                                @elseif(($row['type'] ?? null) === 'url' && filter_var($row['value'], FILTER_VALIDATE_URL) && in_array(parse_url($row['value'], PHP_URL_SCHEME), ['http', 'https'], true))
                                                    <a href="{{ $row['value'] }}" target="_blank" rel="noopener noreferrer">{{ $row['value'] }}</a>
                                                @else
                                                    {{ $row['value'] ?: '-' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                @if($profileSections->isNotEmpty())
                    @php($sectionAnchors = ['history' => 'sejarah-desa', 'vision' => 'visi-dan-misi', 'mission' => 'misi-desa', 'profile' => 'profil-desa'])
                    @foreach($profileSections as $section)
                        <section id="{{ $sectionAnchors[$section->section_key] ?? $section->section_key }}">
                            <h2>{{ $section->title }}</h2>
                            @if($section->image && !in_array($section->section_key, ['vision', 'mission'], true))
                                <img class="profile-section-image" src="{{ $section->image->url }}" alt="{{ $section->image->alt_text ?: $section->title }}">
                            @endif
                            <div>{!! $section->content !!}</div>
                        </section>
                    @endforeach
                @else
                <section id="sejarah-desa">
                    <h2>Sejarah Singkat</h2>
                    <p>Desa Sukomulyo berkembang dari kehidupan masyarakat yang menjunjung kebersamaan, kerja keras, dan gotong royong. Nilai tersebut menjadi dasar dalam setiap kegiatan sosial maupun pembangunan desa.</p>
                    <p>Informasi sejarah pada halaman ini disiapkan sebagai struktur awal dan dapat disesuaikan dengan data resmi serta cerita para tokoh masyarakat.</p>
                </section>

                <section id="visi-dan-misi" class="vision-box">
                    <span class="section-kicker">Visi</span>
                    <blockquote>Terwujudnya Desa Sukomulyo yang maju, mandiri, sejahtera, dan berkarakter melalui tata kelola pemerintahan yang melayani.</blockquote>
                </section>

                <section id="misi-desa">
                    <h2>Misi Desa</h2>
                    <ol class="mission-list">
                        <li>Meningkatkan kualitas pelayanan publik yang cepat, terbuka, dan bertanggung jawab.</li>
                        <li>Mendorong pembangunan desa berdasarkan kebutuhan serta partisipasi masyarakat.</li>
                        <li>Mengembangkan potensi pertanian, UMKM, sosial, seni, dan budaya lokal.</li>
                        <li>Meningkatkan kualitas lingkungan, kesehatan, pendidikan, dan kesejahteraan warga.</li>
                    </ol>
                </section>

                @endif
            </article>

            <x-sidebar :categories="$categories" :archive-years="$archiveYears" />
            <div class="clear"></div>
        </div>
    </div>
</x-layouts.app>
