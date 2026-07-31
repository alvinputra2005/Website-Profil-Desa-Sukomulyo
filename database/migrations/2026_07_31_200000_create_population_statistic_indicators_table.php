<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('population_statistic_indicators')) {
            Schema::create('population_statistic_indicators', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('label');
                $table->string('slug')->unique();
                $table->string('source_table');
                $table->string('source_column')->nullable();
                $table->string('aggregation_type', 40);
                $table->json('configuration_json')->nullable();
                $table->string('unit', 30)->default('jiwa');
                $table->string('chart_type', 30)->default('bar');
                $table->string('icon', 100)->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_public')->default(false);
                $table->unsignedInteger('display_order')->default(0);
                $table->timestamps();

                $table->index(['is_enabled', 'display_order']);
            });
        }

        DB::table('population_statistic_indicators')->updateOrInsert(
            ['key' => 'gender'],
            [
                'label' => 'Jenis Kelamin',
                'slug' => 'jenis-kelamin',
                'source_table' => 'residents',
                'source_column' => 'sex',
                'aggregation_type' => 'categorical',
                'configuration_json' => json_encode(['labels' => ['L' => 'Laki-laki', 'P' => 'Perempuan'], 'include_unknown' => true]),
                'unit' => 'jiwa',
                'chart_type' => 'doughnut',
                'icon' => 'fa-venus-mars',
                'is_enabled' => true,
                'is_public' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        DB::table('population_statistic_indicators')->updateOrInsert(
            ['key' => 'age_range'],
            [
                'label' => 'Rentang Umur',
                'slug' => 'rentang-umur',
                'source_table' => 'residents',
                'source_column' => 'birth_date',
                'aggregation_type' => 'age_range',
                'configuration_json' => json_encode([
                    'source_mode' => 'birth_date',
                    'ranges' => [
                        ['key' => '0_4', 'label' => '0–4 Tahun', 'min' => 0, 'max' => 4],
                        ['key' => '5_14', 'label' => '5–14 Tahun', 'min' => 5, 'max' => 14],
                        ['key' => '15_24', 'label' => '15–24 Tahun', 'min' => 15, 'max' => 24],
                        ['key' => '25_44', 'label' => '25–44 Tahun', 'min' => 25, 'max' => 44],
                        ['key' => '45_64', 'label' => '45–64 Tahun', 'min' => 45, 'max' => 64],
                        ['key' => '65_plus', 'label' => '65+ Tahun', 'min' => 65, 'max' => null],
                    ],
                ]),
                'unit' => 'jiwa',
                'chart_type' => 'bar',
                'icon' => 'fa-bar-chart',
                'is_enabled' => true,
                'is_public' => true,
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('population_statistic_indicators');
    }
};
