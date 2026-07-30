<?php

namespace Database\Seeders;

use App\Models\IdmScore;
use App\Models\StatisticDataset;
use App\Models\StatisticValue;
use App\Models\User;
use App\Services\SiteCache;
use Illuminate\Database\Seeder;
use RuntimeException;

class VillageStatisticDemoSeeder extends Seeder
{
    public const SOURCE = 'Data dummy lokal statistik Desa Sukomulyo';

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Seeder statistik dummy hanya boleh dijalankan pada environment local atau testing.');
        }

        $creator = User::query()->first();
        if (! $creator) {
            throw new RuntimeException('Jalankan DatabaseSeeder terlebih dahulu agar pengguna pembuat data tersedia.');
        }

        $currentYear = (int) config('village.population_year', now()->year);
        $firstYear = $currentYear - 4;
        $contexts = config('statistic_pages.contexts', []);

        StatisticDataset::query()
            ->where('source', self::SOURCE)
            ->whereNotIn('category', array_keys($contexts))
            ->delete();

        foreach ($contexts as $context => $definition) {
            foreach (range($firstYear, $currentYear) as $year) {
                $existingRealDataset = StatisticDataset::query()
                    ->where('category', $context)
                    ->where('year', $year)
                    ->where(fn ($query) => $query
                        ->whereNull('source')
                        ->orWhere('source', '!=', self::SOURCE))
                    ->exists();

                if ($existingRealDataset) {
                    continue;
                }

                $dataset = StatisticDataset::query()->updateOrCreate(
                    ['slug' => "{$context}-{$year}"],
                    [
                        'category' => $context,
                        'title' => $definition['title'].' '.$year,
                        'description' => $definition['description'],
                        'year' => $year,
                        'unit' => $definition['unit'] ?? 'data',
                        'visualization_type' => $definition['chart'] ?? 'bar',
                        'source' => self::SOURCE,
                        'status' => 'published',
                        'display_order' => $year,
                        'created_by' => $creator->id,
                    ],
                );

                $offset = $year - $firstYear;
                $configuredLabels = collect($definition['values'])->pluck(0)->all();
                StatisticValue::query()
                    ->where('dataset_id', $dataset->id)
                    ->whereNotIn('label', $configuredLabels)
                    ->delete();

                foreach ($definition['values'] as $order => $valueDefinition) {
                    [$label, $base, $growth] = $valueDefinition;
                    $unit = $valueDefinition[3] ?? ($definition['unit'] ?? 'data');
                    $decimals = (int) ($definition['decimals'] ?? 0);
                    $value = max(0, round($base + ($growth * $offset), $decimals));

                    StatisticValue::query()->updateOrCreate(
                        ['dataset_id' => $dataset->id, 'label' => $label],
                        [
                            'value' => $value,
                            'secondary_value' => null,
                            'display_order' => $order,
                            'metadata_json' => ['unit' => $unit, 'dummy' => true],
                        ],
                    );
                }
            }
        }

        $idmDefinition = $contexts['idm'] ?? null;
        if ($idmDefinition) {
            foreach (range($firstYear, $currentYear) as $year) {
                $offset = $year - $firstYear;
                $values = collect($idmDefinition['values'])->mapWithKeys(
                    fn (array $item): array => [$item[0] => round($item[1] + ($item[2] * $offset), 4)],
                );
                IdmScore::query()->updateOrCreate(
                    ['year' => $year],
                    [
                        'idm_score' => $values['IDM'],
                        'iks_score' => $values['IKS'],
                        'ike_score' => $values['IKE'],
                        'ikl_score' => $values['IKL'],
                        'status_label' => $values['IDM'] >= .75 ? 'Maju' : 'Berkembang',
                        'source' => self::SOURCE,
                    ],
                );
            }
        }

        app(SiteCache::class)->invalidatePopulationStatistics();
    }
}
