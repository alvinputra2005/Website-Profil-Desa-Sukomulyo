<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('village_identities')) {
            return;
        }

        foreach ([
            'email' => ['pemdes@sukomulyo.desa.id', 'desasukomulyo2022@gmail.com'],
            'phone' => ['(0000) 123 456', '085731625435'],
        ] as $column => [$oldValue, $newValue]) {
            Schema::getConnection()->table('village_identities')
                ->where($column, $oldValue)
                ->update([$column => $newValue, 'updated_at' => now()]);
        }

        Cache::forget('site.village-identity.public');
        Cache::forget('site.layout.public');
    }

    public function down(): void
    {
        if (! Schema::hasTable('village_identities')) {
            return;
        }

        foreach ([
            'email' => ['desasukomulyo2022@gmail.com', 'pemdes@sukomulyo.desa.id'],
            'phone' => ['085731625435', '(0000) 123 456'],
        ] as $column => [$newValue, $oldValue]) {
            Schema::getConnection()->table('village_identities')
                ->where($column, $newValue)
                ->update([$column => $oldValue, 'updated_at' => now()]);
        }
    }
};
