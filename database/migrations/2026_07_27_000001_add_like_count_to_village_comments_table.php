<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('village_comments', function (Blueprint $table) {
            $table->unsignedInteger('like_count')->default(0)->after('comment');
        });

        if (! DB::table('village_comments')->exists()) {
            $now = now();

            DB::table('village_comments')->insert([
                [
                    'name' => 'Siti Aminah',
                    'address' => 'Desa Sukomulyo',
                    'phone' => '0000000000',
                    'comment' => 'Informasi identitas desa sudah jelas dan sangat membantu warga.',
                    'like_count' => 12,
                    'is_visible' => true,
                    'created_at' => $now->copy()->subHours(2),
                    'updated_at' => $now->copy()->subHours(2),
                ],
                [
                    'name' => 'Budi Santoso',
                    'address' => 'Desa Sukomulyo',
                    'phone' => '0000000001',
                    'comment' => 'Semoga data dan layanan desa terus diperbarui seperti ini.',
                    'like_count' => 8,
                    'is_visible' => true,
                    'created_at' => $now->copy()->subDay(),
                    'updated_at' => $now->copy()->subDay(),
                ],
                [
                    'name' => 'Rina Wulandari',
                    'address' => 'Desa Sukomulyo',
                    'phone' => '0000000002',
                    'comment' => 'Tampilan informasinya rapi, jadi mudah dicari dari ponsel.',
                    'like_count' => 5,
                    'is_visible' => true,
                    'created_at' => $now->copy()->subDays(2),
                    'updated_at' => $now->copy()->subDays(2),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('village_comments', function (Blueprint $table) {
            $table->dropColumn('like_count');
        });
    }
};
