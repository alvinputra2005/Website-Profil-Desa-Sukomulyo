<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        foreach ([
            'site.email' => ['pemdes@sukomulyo.desa.id', 'desasukomulyo2022@gmail.com'],
            'site.phone' => ['(0000) 123 456', '085731625435'],
        ] as $key => [$oldValue, $newValue]) {
            DB::table('settings')
                ->where('key', $key)
                ->where('value', $oldValue)
                ->update(['value' => $newValue, 'updated_at' => now()]);
        }

        Cache::forget('site.settings.public');
        Cache::forget('site.layout.public');
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        foreach ([
            'site.email' => ['desasukomulyo2022@gmail.com', 'pemdes@sukomulyo.desa.id'],
            'site.phone' => ['085731625435', '(0000) 123 456'],
        ] as $key => [$newValue, $oldValue]) {
            DB::table('settings')
                ->where('key', $key)
                ->where('value', $newValue)
                ->update(['value' => $oldValue, 'updated_at' => now()]);
        }
    }
};
