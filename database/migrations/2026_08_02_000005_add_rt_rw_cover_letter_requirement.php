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

        $service = DB::table('letter_services')->where('code', 'SKTM')->first();
        if (! $service) {
            return;
        }

        $requirements = json_decode((string) $service->requirements_json, true);
        $requirements = is_array($requirements) ? $requirements : [];

        $alreadyExists = collect($requirements)->contains(
            fn (array $requirement): bool => ($requirement['key'] ?? null) === 'surat-pengantar-rt-rw',
        );

        if (! $alreadyExists) {
            $requirements[] = [
                'key' => 'surat-pengantar-rt-rw',
                'label' => 'Surat Pengantar RT/RW',
                'description' => 'Surat pengantar yang telah ditandatangani oleh pengurus RT/RW setempat.',
                'required' => true,
            ];

            DB::table('letter_services')->where('id', $service->id)->update([
                'requirements_json' => json_encode($requirements, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('letter_services')) {
            return;
        }

        $service = DB::table('letter_services')->where('code', 'SKTM')->first();
        if (! $service) {
            return;
        }

        $requirements = collect(json_decode((string) $service->requirements_json, true) ?: [])
            ->reject(fn (array $requirement): bool => ($requirement['key'] ?? null) === 'surat-pengantar-rt-rw')
            ->values()
            ->all();

        DB::table('letter_services')->where('id', $service->id)->update([
            'requirements_json' => json_encode($requirements, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }
};
