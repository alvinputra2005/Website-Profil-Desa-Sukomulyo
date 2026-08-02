<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('letter_services')) {
            return;
        }

        DB::table('letter_services')
            ->where('code', 'SKTM')
            ->update([
                'name' => 'Surat Keterangan',
                'slug' => 'surat-keterangan',
                'description' => 'Layanan permohonan awal Surat Keterangan. Surat fisik diambil di kantor desa setelah dinyatakan siap.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('letter_services')) {
            return;
        }

        DB::table('letter_services')
            ->where('code', 'SKTM')
            ->update([
                'name' => 'Surat Keterangan Tidak Mampu',
                'slug' => 'surat-keterangan-tidak-mampu',
                'description' => 'Layanan permohonan awal Surat Keterangan Tidak Mampu. Surat fisik diambil di kantor desa setelah dinyatakan siap.',
                'updated_at' => now(),
            ]);
    }
};
