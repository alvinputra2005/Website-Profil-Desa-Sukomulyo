<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('village_profile_sections')) {
            return;
        }

        $profile = DB::table('village_profile_sections')
            ->where('section_key', 'profile')
            ->first();

        if (! $profile || ! str_contains((string) $profile->content, '6.652 jiwa')) {
            return;
        }

        DB::table('village_profile_sections')
            ->where('id', $profile->id)
            ->update([
                'title' => 'Desa Sukomulyo',
                'content' => <<<'HTML'
<p>Desa Sukomulyo merupakan desa yang berada di Kecamatan Pujon, Kabupaten Malang, Provinsi Jawa Timur, dengan kode pos 65391. Berada di kawasan dataran tinggi Kecamatan Pujon, desa ini menjadi ruang hidup masyarakat yang tumbuh dengan karakter perdesaan yang kuat, keterikatan sosial antarmasyarakat, serta semangat gotong royong dalam menjalankan kehidupan sehari-hari. Letaknya berbatasan dengan Desa Ngabab di sebelah utara, Desa Bendosari di sebelah barat, kawasan hutan atau Gunung Kawi di sebelah selatan, serta Desa Pujon Kidul di sebelah timur.</p>

<p>Jumlah penduduk Desa Sukomulyo tercatat sebanyak <strong>6.565 jiwa</strong>. Penduduk tersebut tersebar dalam lima wilayah dusun, yaitu Dusun Bakir, Dusun Biyan, Dusun Gumul, Dusun Talasan, dan Dusun Kedungrejo. Pembagian wilayah ini menjadi dasar penyelenggaraan pelayanan pemerintahan, pembinaan kemasyarakatan, serta pelaksanaan pembangunan yang lebih dekat dengan kebutuhan warga di setiap lingkungan.</p>

<p>Secara administratif, Desa Sukomulyo didukung oleh 11 Rukun Warga dan 45 Rukun Tetangga yang tersebar di lima dusun. Setiap dusun dipimpin oleh kepala dusun yang berperan sebagai penghubung antara pemerintah desa dan masyarakat. Susunan wilayah tersebut membantu penyampaian informasi, pengelolaan administrasi kependudukan, penanganan kebutuhan sosial, hingga pelaksanaan kegiatan pembangunan agar dapat berjalan secara terarah dan merata.</p>

<p>Kehidupan masyarakat Desa Sukomulyo ditopang oleh potensi lokal di bidang pertanian dan peternakan, serta diperkuat oleh kegiatan perkebunan, perdagangan, jasa, angkutan, keterampilan, dan industri rumah tangga. Keragaman mata pencaharian tersebut menunjukkan bahwa desa tidak hanya bertumpu pada satu sektor, melainkan memiliki sumber daya manusia dan potensi ekonomi yang dapat terus dikembangkan. Melalui pelayanan publik yang tertib, partisipasi warga, dan kerja sama antara pemerintah desa dengan masyarakat, Desa Sukomulyo terus diarahkan sebagai desa yang aman, maju, dan sejahtera.</p>
HTML,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Konten publik dapat diperbarui oleh administrator, sehingga tidak dihapus saat rollback.
    }
};
