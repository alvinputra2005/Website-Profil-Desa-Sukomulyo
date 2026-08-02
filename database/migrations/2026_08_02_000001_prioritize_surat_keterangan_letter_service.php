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
            ->where('code', '!=', 'SKTM')
            ->increment('display_order');

        DB::table('letter_services')
            ->where('code', 'SKTM')
            ->update(['display_order' => 0, 'updated_at' => now()]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('letter_services')) {
            return;
        }

        DB::table('letter_services')
            ->where('code', '!=', 'SKTM')
            ->where('display_order', '>', 0)
            ->decrement('display_order');
    }
};
