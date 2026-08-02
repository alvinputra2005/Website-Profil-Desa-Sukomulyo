<?php

use App\Models\VillageIdentity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('village_identities')) {
            $this->createVillageIdentitiesTable();
        }

        if (Schema::hasTable('settings')) {
            $legacy = DB::table('settings')
                ->whereIn('key', array_keys(VillageIdentity::KEY_MAP))
                ->pluck('value', 'key');

            if ($legacy->isNotEmpty()) {
                $identity = collect(VillageIdentity::KEY_MAP)
                    ->mapWithKeys(fn (string $column, string $key): array => [$column => $legacy->get($key)])
                    ->filter(fn ($value) => $value !== null)
                    ->all();
                $identity['updated_by'] = DB::table('settings')->whereIn('key', array_keys(VillageIdentity::KEY_MAP))->value('updated_by');
                $identity['updated_at'] = now();
                $identity['created_at'] = now();

                DB::table('village_identities')->updateOrInsert(['id' => 1], $identity);
            }
        }

        Schema::dropIfExists('redirects');
        Schema::dropIfExists('settings');

        foreach (['site.settings.public', 'site.village-identity.public', 'site.layout.public', 'profile.sections'] as $key) {
            Cache::forget($key);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table): void {
                $table->id();
                $table->string('key', 100)->unique();
                $table->longText('value')->nullable();
                $table->string('type', 30)->default('string');
                $table->string('group', 50)->index();
                $table->boolean('is_public')->default(false)->index();
                $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('redirects')) {
            Schema::create('redirects', function (Blueprint $table): void {
                $table->id();
                $table->string('old_path', 500)->unique();
                $table->string('new_path', 500)->index();
                $table->unsignedSmallInteger('status_code')->default(301);
                $table->timestamps();
            });
        }
    }

    private function createVillageIdentitiesTable(): void
    {
        Schema::create('village_identities', function (Blueprint $table): void {
            $table->id();
            $table->string('site_name')->default('Desa Sukomulyo');
            $table->string('tagline')->nullable();
            $table->string('village_code', 20)->nullable();
            $table->string('village_bps_code', 20)->nullable();
            $table->string('postal_code', 5)->nullable();
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('website')->nullable();
            $table->string('district_name', 100)->nullable();
            $table->string('district_code', 20)->nullable();
            $table->string('district_head_name')->nullable();
            $table->string('district_head_nip', 30)->nullable();
            $table->string('regency_name', 100)->nullable();
            $table->string('regency_code', 20)->nullable();
            $table->string('province_name', 100)->nullable();
            $table->string('province_code', 20)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
};
