<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use App\Models\VillageProfileSection;
use App\Services\HtmlSanitizer;
use App\Services\SiteCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VillageProfileContentSeeder extends Seeder
{
    private const SETTINGS = [
        'site.name' => 'Desa Sukomulyo',
        'site.tagline' => 'Website Resmi Pemerintah Desa Sukomulyo',
        'village.code' => '35.07.26.2002',
        'village.postal_code' => '65391',
        'district.name' => 'Pujon',
        'district.code' => '35.07.26',
        'regency.name' => 'Kabupaten Malang',
        'regency.code' => '35.07',
        'province.name' => 'Jawa Timur',
        'province.code' => '35',
        'site.address' => 'Kantor Desa Sukomulyo, Kecamatan Pujon, Kabupaten Malang, Jawa Timur 65391',
    ];

    private const REPLACEABLE_SETTINGS = [
        'site.name' => ['Nama Desa'],
        'site.tagline' => ['Website Resmi Desa'],
        'site.address' => ['Kantor Desa Sukomulyo, Indonesia'],
    ];

    private const REPLACEABLE_CONTENTS = [
        'history' => [
            '<p>Desa Sukomulyo tumbuh melalui semangat gotong royong masyarakat.</p>',
        ],
        'vision' => [
            '<p>Terwujudnya desa yang maju, mandiri, transparan, dan sejahtera.</p>',
        ],
        'mission' => [
            '<p>Meningkatkan pelayanan publik, ekonomi warga, dan pembangunan berkelanjutan.</p>',
        ],
    ];

    public function run(): void
    {
        $adminId = $this->adminId();
        $sanitizer = app(HtmlSanitizer::class);

        DB::transaction(function () use ($adminId, $sanitizer): void {
            foreach (self::SETTINGS as $key => $value) {
                $this->seedSetting($key, $value, $adminId);
            }

            $this->seedSection('profile', [
                'title' => 'Ringkasan Identitas Desa Sukomulyo',
                'content' => <<<'HTML'
<p>Desa Sukomulyo merupakan desa yang berada di Kecamatan Pujon, Kabupaten Malang, Provinsi Jawa Timur, dengan kode pos 65391. Berada di kawasan dataran tinggi Kecamatan Pujon, desa ini menjadi ruang hidup masyarakat yang tumbuh dengan karakter perdesaan yang kuat, keterikatan sosial antarmasyarakat, serta semangat gotong royong dalam menjalankan kehidupan sehari-hari. Letaknya berbatasan dengan Desa Ngabab di sebelah utara, Desa Bendosari di sebelah barat, kawasan hutan atau Gunung Kawi di sebelah selatan, serta Desa Pujon Kidul di sebelah timur.</p>

<p>Jumlah penduduk Desa Sukomulyo tercatat sebanyak <strong>6.565 jiwa</strong>. Penduduk tersebut tersebar dalam lima wilayah dusun, yaitu Dusun Bakir, Dusun Biyan, Dusun Gumul, Dusun Talasan, dan Dusun Kedungrejo. Pembagian wilayah ini menjadi dasar penyelenggaraan pelayanan pemerintahan, pembinaan kemasyarakatan, serta pelaksanaan pembangunan yang lebih dekat dengan kebutuhan warga di setiap lingkungan.</p>

<p>Secara administratif, Desa Sukomulyo didukung oleh 11 Rukun Warga dan 45 Rukun Tetangga yang tersebar di lima dusun. Setiap dusun dipimpin oleh kepala dusun yang berperan sebagai penghubung antara pemerintah desa dan masyarakat. Susunan wilayah tersebut membantu penyampaian informasi, pengelolaan administrasi kependudukan, penanganan kebutuhan sosial, hingga pelaksanaan kegiatan pembangunan agar dapat berjalan secara terarah dan merata.</p>

<p>Kehidupan masyarakat Desa Sukomulyo ditopang oleh potensi lokal di bidang pertanian dan peternakan, serta diperkuat oleh kegiatan perkebunan, perdagangan, jasa, angkutan, keterampilan, dan industri rumah tangga. Keragaman mata pencaharian tersebut menunjukkan bahwa desa tidak hanya bertumpu pada satu sektor, melainkan memiliki sumber daya manusia dan potensi ekonomi yang dapat terus dikembangkan. Melalui pelayanan publik yang tertib, partisipasi warga, dan kerja sama antara pemerintah desa dengan masyarakat, Desa Sukomulyo terus diarahkan sebagai desa yang aman, maju, dan sejahtera.</p>
HTML,
                'status' => 'published',
                'display_order' => 0,
            ], $adminId, $sanitizer);

            $this->seedSection('history', [
                'title' => 'Sejarah Desa Sukomulyo',
                'content' => <<<'HTML'
<h2>Asal-Usul Desa Sukomulyo</h2>

<p>Berdasarkan cerita rakyat yang dihimpun dalam dokumen profil desa, wilayah Desa Sukomulyo pada masa dahulu masih berupa hutan belantara. Dalam kisah tersebut, Mbah Syekh Subakir yang berasal dari Negeri Irak datang dan membuka kawasan hutan. Wilayah yang dibuka tersebut kemudian dikenal dengan nama Bakir.</p>

<p>Kisah desa selanjutnya menyebut Mbah Niti Seno sebagai salah satu tokoh awal di Dusun Bakir. Mbah Niti Seno memiliki seorang anak bernama Mbah Roso Joyo yang konon mempunyai seekor gajah bernama Gajah Oling.</p>

<p>Seiring bertambahnya penduduk, Mbah Roso Joyo menggagas pembentukan sebuah kademangan atau desa. Wilayah tersebut kemudian diberi nama Sukomulyo. Dalam dokumen profil desa, nama Sukomulyo dimaknai dari kata <strong>Suko</strong> yang berarti senang dan <strong>Mulyo</strong> yang berarti makmur.</p>

<p>Mbah Roso Joyo kemudian mengajak para tokoh pembuka wilayah atau bedah kerawang dari lima dusun untuk bermusyawarah membentuk pemerintahan desa. Hasil musyawarah menetapkan Demang Joyo Karto sebagai demang pertama Desa Sukomulyo.</p>

<h2>Bedah Kerawang Lima Dusun</h2>

<table>
    <thead>
        <tr>
            <th scope="col">No.</th>
            <th scope="col">Nama Kerawang</th>
            <th scope="col">Dusun</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>Mbah Syekh Subakir</td><td>Bakir</td></tr>
        <tr><td>2</td><td>Mbah Songgolo</td><td>Biyan</td></tr>
        <tr><td>3</td><td>Eyang Woro Wiro Karto Diharjo</td><td>Gumul</td></tr>
        <tr><td>4</td><td>Eyang Kerto Suro</td><td>Talasan</td></tr>
        <tr><td>5</td><td>Eyang Cablek</td><td>Kedungrejo</td></tr>
    </tbody>
</table>

<h2>Riwayat Pemimpin Desa Sukomulyo</h2>

<table>
    <thead>
        <tr>
            <th scope="col">No.</th>
            <th scope="col">Nama Pemimpin</th>
            <th scope="col">Masa Jabatan</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>Demang Joyo Karto</td><td>1740–1788</td></tr>
        <tr><td>2</td><td>Demang Joyo Negoro</td><td>1788–1818</td></tr>
        <tr><td>3</td><td>Demang Joyo Tonggolo</td><td>1818–1860</td></tr>
        <tr><td>4</td><td>Ki Lurah Brojo</td><td>1860–1895</td></tr>
        <tr><td>5</td><td>Kepala Desa Sekak</td><td>1895–1921</td></tr>
        <tr><td>6</td><td>Kepala Desa Wiryorejo</td><td>1921–1953</td></tr>
        <tr><td>7</td><td>Kepala Desa Takrib</td><td>1953–1964</td></tr>
        <tr><td>8</td><td>Kepala Desa Subanu</td><td>1964–1972</td></tr>
        <tr><td>9</td><td>Kepala Desa Banjar Sukoprayitno</td><td>1972–1980</td></tr>
        <tr><td>10</td><td>Kepala Desa Saan Harjomulyo</td><td>1980–1988</td></tr>
        <tr><td>11</td><td>Kepala Desa Achmad Suroso</td><td>1988–1996</td></tr>
        <tr><td>12</td><td>Kepala Desa Suyanto, S.Pd.</td><td>1996–2002</td></tr>
        <tr><td>13</td><td>Kepala Desa H. Suhadi</td><td>2002–2012</td></tr>
        <tr><td>14</td><td>Kepala Desa Safiul Anwar, ST</td><td>2012–2018</td></tr>
        <tr><td>15</td><td>Kepala Desa Safiul Anwar, ST</td><td>2019–2024</td></tr>
    </tbody>
</table>

<h2>Perkembangan Pembangunan Desa</h2>

<p>Perjalanan pembangunan Desa Sukomulyo mengalami perkembangan dalam bidang ekonomi, sosial, dan lingkungan. Pada masa sebelumnya, sebagian jalan desa masih berupa jalan tanah yang sulit dilalui ketika musim hujan. Kondisi bangunan, rumah masyarakat, dan fasilitas umum juga masih sederhana dan terbatas. Pendapatan rata-rata masyarakat serta kualitas sumber daya manusia pada saat itu masih relatif rendah.</p>

<p>Melalui kerja sama pemerintah desa, masyarakat, dan berbagai pihak, pembangunan dilakukan secara bertahap. Perbaikan sarana dan prasarana, peningkatan pelayanan, serta pengembangan kegiatan ekonomi telah memberikan perubahan yang dapat dirasakan masyarakat. Pembangunan tersebut dilaksanakan melalui swadaya masyarakat, dana desa, APBD, dukungan pihak swasta, dan berbagai program pembangunan lainnya.</p>
HTML,
                'status' => 'published',
                'display_order' => 5,
            ], $adminId, $sanitizer, self::REPLACEABLE_CONTENTS['history']);

            $this->upgradeHistoryPresentation($adminId, $sanitizer);

            $this->seedSection('vision', [
                'title' => 'Visi Desa Sukomulyo',
                'content' => <<<'HTML'
<blockquote>
    <p><strong>Mewujudkan Desa Sukomulyo yang aman maju dan sejahtera</strong></p>
</blockquote>

<p>Visi tersebut dirumuskan dalam penyusunan RPJM Desa Sukomulyo periode 2019–2024 melalui rangkaian musyawarah yang melibatkan pemerintah desa, lembaga desa, masyarakat, dan pihak yang berkepentingan.</p>

<p>Melalui visi tersebut, Desa Sukomulyo diarahkan menjadi desa yang aman, maju, dan sejahtera, dengan kemajuan pada sektor pertanian serta kehidupan masyarakat yang rukun dan makmur. Pembangunan desa juga diarahkan untuk mendorong inovasi pada bidang pertanian, perkebunan, peternakan, pertukangan, dan kebudayaan yang ditopang oleh nilai-nilai keagamaan.</p>
HTML,
                'status' => 'published',
                'display_order' => 10,
            ], $adminId, $sanitizer, self::REPLACEABLE_CONTENTS['vision']);

            $this->seedSection('mission', [
                'title' => 'Misi Desa Sukomulyo',
                'content' => <<<'HTML'
<p>Untuk mewujudkan visi Desa Sukomulyo, ditetapkan misi sebagai berikut:</p>

<ol>
    <li>Mewujudkan dan mengembangkan kegiatan keagamaan untuk meningkatkan keimanan dan ketakwaan kepada Tuhan Yang Maha Esa.</li>
    <li>Mewujudkan dan mendorong kerukunan antarwarga maupun di dalam lingkungan masyarakat yang memiliki perbedaan agama, keyakinan, organisasi, dan latar belakang lainnya dalam suasana saling menghargai dan menghormati.</li>
    <li>Membangun dan meningkatkan hasil pertanian melalui penataan pengairan, perbaikan jalan sawah atau jalan usaha tani, pemupukan, dan penerapan pola tanam yang baik.</li>
    <li>Menata Pemerintahan Desa Sukomulyo yang kompak dan bertanggung jawab dalam mengemban amanat masyarakat.</li>
    <li>Meningkatkan pelayanan kepada masyarakat secara terpadu dan sungguh-sungguh.</li>
    <li>Mencari dan menambah debit air untuk mencukupi kebutuhan pertanian.</li>
    <li>Menumbuhkembangkan kelompok tani dan gabungan kelompok tani serta bekerja sama dengan BUMDes untuk memfasilitasi kebutuhan petani.</li>
    <li>Menumbuhkembangkan usaha kecil dan menengah.</li>
    <li>Bekerja sama dengan instansi yang membidangi kehutanan dan perkebunan dalam melestarikan lingkungan hidup.</li>
    <li>Membangun dan mendorong kemajuan pendidikan formal maupun informal yang mudah diakses dan dinikmati seluruh masyarakat tanpa terkecuali, serta mampu menghasilkan insan yang intelektual, inovatif, dan berjiwa wirausaha.</li>
    <li>Membangun dan mendorong pengembangan serta optimalisasi sektor pertanian, perkebunan, peternakan, dan perikanan, baik pada tahap produksi maupun pengolahan hasil.</li>
</ol>
HTML,
                'status' => 'published',
                'display_order' => 20,
            ], $adminId, $sanitizer, self::REPLACEABLE_CONTENTS['mission']);
        });

        $siteCache = app(SiteCache::class);
        $siteCache->invalidateSettings();
        $siteCache->invalidateProfile();
    }

    private function adminId(): int
    {
        $admin = User::query()
            ->where('email', 'admin@sukomulyo.desa.id')
            ->first()
            ?? User::query()
                ->whereHas('role', fn ($query) => $query->where('code', 'super_admin'))
                ->first();

        if (! $admin) {
            throw new RuntimeException('VillageProfileContentSeeder memerlukan akun super admin yang sudah tersedia.');
        }

        return $admin->id;
    }

    private function seedSetting(string $key, string $value, int $adminId): void
    {
        $setting = Setting::query()->firstOrNew(['key' => $key]);
        $currentValue = trim((string) $setting->value);
        $mayReplace = ! $setting->exists
            || $currentValue === ''
            || in_array($currentValue, self::REPLACEABLE_SETTINGS[$key] ?? [], true);

        if (! $mayReplace) {
            return;
        }

        $setting->fill([
            'value' => $value,
            'type' => $key === 'site.address' ? 'text' : 'string',
            'group' => 'identitas',
            'is_public' => true,
            'updated_by' => $adminId,
        ])->save();
    }

    private function seedSection(
        string $sectionKey,
        array $attributes,
        int $adminId,
        HtmlSanitizer $sanitizer,
        array $replaceableContents = [],
    ): void {
        $section = VillageProfileSection::query()->firstOrNew(['section_key' => $sectionKey]);
        $currentContent = trim((string) $section->content);
        $mayReplace = ! $section->exists
            || $currentContent === ''
            || in_array($currentContent, $replaceableContents, true);

        if (! $mayReplace) {
            return;
        }

        $section->fill([
            ...$attributes,
            'content' => $sanitizer->clean($attributes['content']),
            'updated_by' => $adminId,
        ])->save();
    }

    private function upgradeHistoryPresentation(int $adminId, HtmlSanitizer $sanitizer): void
    {
        $history = VillageProfileSection::query()->where('section_key', 'history')->first();

        if (! $history) {
            return;
        }

        $legacyHamletList = $sanitizer->clean(<<<'HTML'
<ul>
    <li><strong>Dusun Bakir:</strong> Mbah Syekh Subakir</li>
    <li><strong>Dusun Biyan:</strong> Mbah Songgolo</li>
    <li><strong>Dusun Gumul:</strong> Eyang Woro Wiro Karto Diharjo</li>
    <li><strong>Dusun Talasan:</strong> Eyang Kerto Suro</li>
    <li><strong>Dusun Kedungrejo:</strong> Eyang Cablek</li>
</ul>
HTML);
        $legacyHamletCards = $sanitizer->clean(<<<'HTML'
<div class="profile-hamlet-grid">
    <div class="profile-hamlet-card"><strong>Dusun Bakir</strong><p>Mbah Syekh Subakir</p></div>
    <div class="profile-hamlet-card"><strong>Dusun Biyan</strong><p>Mbah Songgolo</p></div>
    <div class="profile-hamlet-card"><strong>Dusun Gumul</strong><p>Eyang Woro Wiro Karto Diharjo</p></div>
    <div class="profile-hamlet-card"><strong>Dusun Talasan</strong><p>Eyang Kerto Suro</p></div>
    <div class="profile-hamlet-card"><strong>Dusun Kedungrejo</strong><p>Eyang Cablek</p></div>
</div>
HTML);
        $hamletTable = $sanitizer->clean(<<<'HTML'
<table>
    <thead>
        <tr>
            <th scope="col">No.</th>
            <th scope="col">Nama Kerawang</th>
            <th scope="col">Dusun</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>Mbah Syekh Subakir</td><td>Bakir</td></tr>
        <tr><td>2</td><td>Mbah Songgolo</td><td>Biyan</td></tr>
        <tr><td>3</td><td>Eyang Woro Wiro Karto Diharjo</td><td>Gumul</td></tr>
        <tr><td>4</td><td>Eyang Kerto Suro</td><td>Talasan</td></tr>
        <tr><td>5</td><td>Eyang Cablek</td><td>Kedungrejo</td></tr>
    </tbody>
</table>
HTML);

        $content = str_replace(
            [$legacyHamletList, $legacyHamletCards],
            $hamletTable,
            (string) $history->content,
        );
        $content = str_replace(
            ['<th>No.</th>', '<th>Nama Pemimpin</th>', '<th>Masa Jabatan</th>'],
            ['<th scope="col">No.</th>', '<th scope="col">Nama Pemimpin</th>', '<th scope="col">Masa Jabatan</th>'],
            $content,
        );

        if ($content === $history->content) {
            return;
        }

        $history->forceFill([
            'content' => $sanitizer->clean($content),
            'updated_by' => $adminId,
        ])->save();
    }
}
