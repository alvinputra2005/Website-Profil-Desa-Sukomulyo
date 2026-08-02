<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\IdmScore;
use App\Models\Role;
use App\Models\Setting;
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
        foreach ([
            'letter_service.whatsapp_number' => env('VILLAGE_WHATSAPP_NUMBER', ''),
            'letter_service.office_hours' => env('LETTER_OFFICE_HOURS', 'Senin-Jumat, 08.00-14.00 WIB'),
            'letter_service.pickup_address' => env('LETTER_PICKUP_ADDRESS', 'Kantor Desa Sukomulyo'),
            'letter_service.tracking_retention_days' => env('LETTER_TRACKING_RETENTION_DAYS', 90),
            'letter_service.enabled' => true,
        ] as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => (string) $value, 'type' => is_bool($value) ? 'boolean' : 'string', 'group' => 'pelayanan_surat', 'is_public' => false, 'updated_by' => $admin->id]);
        }
        $this->call(GovernmentOfficialSeeder::class);
        $this->call(NewsSeeder::class);
        $this->call(AnnouncementSeeder::class);
        $this->call(GallerySeeder::class);
        $this->call(LetterServiceSeeder::class);
        Gallery::firstOrCreate(['slug' => 'kegiatan-desa'], ['title' => 'Kegiatan Desa', 'description' => 'Dokumentasi kegiatan warga Desa Sukomulyo.', 'status' => 'published', 'created_by' => $admin->id]);
        StatisticDataset::firstOrCreate(['slug' => 'jumlah-penduduk'], ['category' => 'penduduk', 'title' => 'Jumlah Penduduk', 'description' => 'Statistik jumlah penduduk desa.', 'year' => now()->year, 'unit' => 'jiwa', 'visualization_type' => 'bar', 'status' => 'published', 'display_order' => 0, 'created_by' => $admin->id]);
        IdmScore::firstOrCreate(['year' => now()->year], ['idm_score' => 0.7500, 'iks_score' => 0.7600, 'ike_score' => 0.7300, 'ikl_score' => 0.7600, 'status_label' => 'Maju', 'source' => 'Data awal desa']);
        if (app()->environment(['local', 'testing'])) {
            $this->call(PopulationStatisticsDemoSeeder::class);
            $this->call(VillageStatisticDemoSeeder::class);
        }
    }
}
