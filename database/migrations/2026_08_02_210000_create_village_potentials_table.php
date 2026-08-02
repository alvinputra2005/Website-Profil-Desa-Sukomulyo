<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('village_potentials', function (Blueprint $t) {
            $t->id(); $t->string('title'); $t->string('slug')->unique();
            $t->string('category')->nullable(); $t->string('alternate_name')->nullable();
            $t->longText('description'); $t->json('highlights_json')->nullable();
            $t->text('address')->nullable(); $t->string('map_embed_url', 500)->nullable();
            $t->string('directions_url', 500)->nullable(); $t->foreignId('image_id')->nullable()->constrained('media')->nullOnDelete();
            $t->string('status', 20)->default('draft'); $t->unsignedInteger('display_order')->default(0); $t->timestamps();
            $t->index(['status', 'display_order']);
        });
        DB::table('village_potentials')->insert([
            ['title'=>'Taman Merak Pujon','slug'=>'taman-merak','category'=>'Wisata Alam & Perkemahan','alternate_name'=>null,'description'=>'Taman Merak Pujon menawarkan suasana sejuk di antara pepohonan dengan area terbuka di tepi aliran sungai. Kawasan ini cocok untuk menikmati piknik, berkemah, outbound, dan kegiatan luar ruang bersama keluarga maupun komunitas.','address'=>'Dusun Bakir, Desa Sukomulyo, Kecamatan Pujon, Kabupaten Malang','directions_url'=>'https://www.google.com/maps/search/?api=1&query=Taman+Merak+Pujon%2C+Bakir%2C+Sukomulyo%2C+Pujon%2C+Malang','status'=>'published','display_order'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['title'=>'Coban Manan','slug'=>'coban-manan','alternate_name'=>'Coban Lembah Ayu','category'=>'Wisata Air Terjun','description'=>'Coban Manan, yang juga dikenal sebagai Coban Lembah Ayu, merupakan wisata air terjun bernuansa alami dengan jalur menuju lokasi yang cukup menantang.','address'=>'Dusun Talasan, Desa Sukomulyo, Kecamatan Pujon, Kabupaten Malang','directions_url'=>'https://www.google.com/maps/search/?api=1&query=Coban+Manan%2C+Talasan%2C+Sukomulyo%2C+Pujon%2C+Malang','status'=>'published','display_order'=>2,'created_at'=>now(),'updated_at'=>now()],
        ]);
    }
    public function down(): void { Schema::dropIfExists('village_potentials'); }
};
