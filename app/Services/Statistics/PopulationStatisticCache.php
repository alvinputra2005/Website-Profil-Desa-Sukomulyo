<?php

namespace App\Services\Statistics;

use Illuminate\Support\Facades\Cache;

final class PopulationStatisticCache
{
    public const ADMIN_INDICATORS = 'statistics.population.available-indicators.admin';

    public const PUBLIC_INDICATORS = 'statistics.population.available-indicators.public';

    public static function flush(): void
    {
        Cache::forget(self::ADMIN_INDICATORS);
        Cache::forget(self::PUBLIC_INDICATORS);
    }
}
