<?php

namespace App\Services\Statistics;

use App\Models\PopulationStatisticIndicator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

final class PopulationStatisticIndicatorService
{
    private const ALLOWED_AGGREGATIONS = ['categorical', 'age_range', 'boolean'];

    public function availableIndicators(bool $publicOnly = false): Collection
    {
        $key = $publicOnly
            ? PopulationStatisticCache::PUBLIC_INDICATORS
            : PopulationStatisticCache::ADMIN_INDICATORS;

        return Cache::remember($key, now()->addMinutes(10), function () use ($publicOnly): Collection {
            if (! Schema::hasTable('population_statistic_indicators')) {
                return collect();
            }

            return PopulationStatisticIndicator::query()
                ->where('is_enabled', true)
                ->when($publicOnly, fn ($query) => $query->where('is_public', true))
                ->orderBy('display_order')
                ->orderBy('label')
                ->get()
                ->filter(fn (PopulationStatisticIndicator $indicator): bool => $this->isAvailable($indicator))
                ->values();
        });
    }

    public function isAvailable(PopulationStatisticIndicator $indicator): bool
    {
        if (! in_array($indicator->aggregation_type, self::ALLOWED_AGGREGATIONS, true)) {
            return false;
        }

        try {
            $this->assertSafeIdentifier($indicator->source_table);
            $this->assertSafeIdentifier((string) $indicator->source_column);
        } catch (InvalidArgumentException) {
            return false;
        }

        if (
            ! Schema::hasTable($indicator->source_table)
            || blank($indicator->source_column)
            || ! Schema::hasColumn($indicator->source_table, $indicator->source_column)
        ) {
            return false;
        }

        return $this->sourceQuery($indicator)
            ->whereNotNull($indicator->source_column)
            ->where($indicator->source_column, '<>', '')
            ->exists();
    }

    public function status(PopulationStatisticIndicator $indicator): string
    {
        if (! $indicator->is_enabled) {
            return 'disabled';
        }

        try {
            $this->assertSafeIdentifier($indicator->source_table);
            $this->assertSafeIdentifier((string) $indicator->source_column);
        } catch (InvalidArgumentException) {
            return 'missing_column';
        }

        if (! Schema::hasTable($indicator->source_table)) {
            return 'missing_table';
        }

        if (blank($indicator->source_column) || ! Schema::hasColumn($indicator->source_table, $indicator->source_column)) {
            return 'missing_column';
        }

        return $this->sourceQuery($indicator)
            ->whereNotNull($indicator->source_column)
            ->where($indicator->source_column, '<>', '')
            ->exists()
                ? 'available'
                : 'empty';
    }

    public function assertSafeIdentifier(string $value): void
    {
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value)) {
            throw new InvalidArgumentException('Identifier sumber statistik tidak valid.');
        }
    }

    public function sourceQuery(PopulationStatisticIndicator $indicator)
    {
        $this->assertSafeIdentifier($indicator->source_table);
        $this->assertSafeIdentifier((string) $indicator->source_column);

        return DB::table($indicator->source_table)
            ->when(
                $indicator->source_table === 'residents' && Schema::hasColumn('residents', 'deleted_at'),
                fn ($query) => $query->whereNull('deleted_at'),
            )
            ->when(
                $indicator->source_table === 'residents' && Schema::hasColumn('residents', 'status'),
                fn ($query) => $query->where('status', 'active'),
            );
    }
}
