<?php

namespace Database\Seeders;

use App\Models\{Gallery,IdmScore,Official,Role,Setting,StatisticDataset,User,VillageProfileSection};
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
        $roles=collect([
            'super_admin'=>'Super Admin','admin_konten'=>'Admin Konten','admin_data'=>'Admin Data',
        ])->mapWithKeys(fn($name,$code)=>[$code=>Role::firstOrCreate(['code'=>$code],['name'=>$name])]);
        $admin=User::firstOrCreate(['email'=>'admin@sukomulyo.desa.id'],['role_id'=>$roles['super_admin']->id,'name'=>'Administrator Desa','password'=>'Sukomulyo123!','is_active'=>true,'email_verified_at'=>now()]);
        foreach(['site.name'=>'Desa Sukomulyo','site.tagline'=>'Website Resmi Pemerintah Desa Sukomulyo','site.email'=>'pemdes@sukomulyo.desa.id','site.phone'=>'(0000) 123 456','site.address'=>'Kantor Desa Sukomulyo, Indonesia'] as $key=>$value) Setting::firstOrCreate(['key'=>$key],['value'=>$value,'type'=>'string','group'=>'identitas','is_public'=>true,'updated_by'=>$admin->id]);
        foreach([['history','Sejarah Desa','Desa Sukomulyo tumbuh melalui semangat gotong royong masyarakat.'],['vision','Visi Desa','Terwujudnya desa yang maju, mandiri, transparan, dan sejahtera.'],['mission','Misi Desa','Meningkatkan pelayanan publik, ekonomi warga, dan pembangunan berkelanjutan.']] as [$key,$title,$content]) VillageProfileSection::firstOrCreate(['section_key'=>$key],['title'=>$title,'content'=>'<p>'.$content.'</p>','status'=>'published','display_order'=>0,'updated_by'=>$admin->id]);
        foreach([['Kepala Desa','Nama Kepala Desa'],['Sekretaris Desa','Nama Sekretaris Desa'],['Kaur Keuangan','Nama Perangkat Desa']] as $i=>[$position,$name]) Official::firstOrCreate(['position'=>$position],['name'=>$name,'display_order'=>$i,'is_active'=>true]);
        $this->call(NewsSeeder::class);
        Gallery::firstOrCreate(['slug'=>'kegiatan-desa'],['title'=>'Kegiatan Desa','description'=>'Dokumentasi kegiatan warga Desa Sukomulyo.','status'=>'published','created_by'=>$admin->id]);
        StatisticDataset::firstOrCreate(['slug'=>'jumlah-penduduk'],['category'=>'penduduk','title'=>'Jumlah Penduduk','description'=>'Statistik jumlah penduduk desa.','year'=>now()->year,'unit'=>'jiwa','visualization_type'=>'bar','status'=>'published','display_order'=>0,'created_by'=>$admin->id]);
        IdmScore::firstOrCreate(['year'=>now()->year],['idm_score'=>0.7500,'iks_score'=>0.7600,'ike_score'=>0.7300,'ikl_score'=>0.7600,'status_label'=>'Maju','source'=>'Data awal desa']);
    }
}
