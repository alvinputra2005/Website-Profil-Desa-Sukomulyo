<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\Role;
use App\Models\StatisticDataset;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = collect([
            'super_admin' => 'Super Admin', 'admin_konten' => 'Admin Konten', 'admin_data' => 'Admin Data',
        ])->mapWithKeys(fn ($name, $code) => [$code => Role::firstOrCreate(['code' => $code], ['name' => $name])]);
        $this->call(StatisticCategorySeeder::class);
        $this->call(PopulationStatisticIndicatorSeeder::class);
        $admin = User::firstOrCreate(['email' => 'admin@sukomulyo.desa.id'], ['role_id' => $roles['super_admin']->id, 'name' => 'Administrator Desa', 'password' => 'Sukomulyo123!', 'is_active' => true, 'email_verified_at' => now()]);
        $this->call(VillageProfileContentSeeder::class);
        $this->call(GovernmentOfficialSeeder::class);
        $this->call(NewsSeeder::class);
        $this->call(AnnouncementSeeder::class);
        $this->call(GallerySeeder::class);
        $this->call(LetterServiceSeeder::class);
        Gallery::firstOrCreate(['slug' => 'kegiatan-desa'], ['title' => 'Kegiatan Desa', 'description' => 'Dokumentasi kegiatan warga Desa Sukomulyo.', 'status' => 'published', 'created_by' => $admin->id]);
        StatisticDataset::firstOrCreate(['slug' => 'jumlah-penduduk'], ['category' => 'penduduk', 'title' => 'Jumlah Penduduk', 'description' => 'Statistik jumlah penduduk desa.', 'year' => now()->year, 'unit' => 'jiwa', 'visualization_type' => 'bar', 'status' => 'published', 'display_order' => 0, 'created_by' => $admin->id]);
        if (app()->environment(['local', 'testing'])) {
            $this->call(PopulationStatisticsDemoSeeder::class);
            $this->call(VillageStatisticDemoSeeder::class);
        }
    }
}
