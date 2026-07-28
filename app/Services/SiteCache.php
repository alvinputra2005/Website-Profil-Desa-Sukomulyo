<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class SiteCache
{
    public const SETTINGS = 'site.settings.public';

    public const PUBLIC_LAYOUT = 'site.layout.public';

    public const PROFILE = 'profile.sections';

    public const HOME_STATISTICS = 'home.statistics';

    public const PUBLIC_STATISTICS = 'statistics.public';

    public const PUBLIC_POPULATION_STATISTICS = 'statistics.population.summary';

    public const PUBLIC_POPULATION_TREND = 'statistics.population.trend';

    public const LATEST_NEWS = 'home.latest-news';

    public const NEWS_LIST = 'news.list.published';

    public const NEWS_CATEGORIES = 'news.categories';

    public const NEWS_ARCHIVES = 'news.archives';

    public const NEWS_POPULAR = 'news.popular';

    public const NEWS_DETAIL_VERSION = 'news.detail.version';

    public const OFFICIALS = 'government.officials.v2';

    public const GALLERY = 'gallery.public.v3';

    public const PUBLICATIONS = 'publications.public';

    public const MAP_GEOJSON = 'map.geojson.public';

    public const SEO_SITEMAP = 'seo.sitemap';

    public const ONE_HOUR = 3600;

    public const THIRTY_MINUTES = 1800;

    public const TEN_MINUTES = 600;

    public function remember(string $key, int $seconds, Closure $resolver): mixed
    {
        return Cache::remember($key, $seconds, $resolver);
    }

    public function newsDetailKey(string $slug): string
    {
        return 'news.detail.v'.$this->newsDetailVersion().'.'.sha1($slug);
    }

    public function invalidateSettings(): void
    {
        Cache::forget(self::SETTINGS);
        Cache::forget(self::PUBLIC_LAYOUT);
        Cache::forget(self::PROFILE);
    }

    public function invalidateProfile(): void
    {
        Cache::forget(self::PROFILE);
    }

    public function invalidateOfficials(): void
    {
        Cache::forget(self::OFFICIALS);
        Cache::forget(self::PROFILE);
    }

    public function invalidateNews(): void
    {
        foreach ([
            self::LATEST_NEWS,
            self::NEWS_LIST,
            self::NEWS_CATEGORIES,
            self::NEWS_ARCHIVES,
            self::NEWS_POPULAR,
            self::PUBLIC_LAYOUT,
            self::SEO_SITEMAP,
        ] as $key) {
            Cache::forget($key);
        }

        $this->bumpNewsDetailVersion();
    }

    public function invalidatePopulationStatistics(): void
    {
        Cache::forget(self::HOME_STATISTICS);
        Cache::forget(self::PUBLIC_STATISTICS);
        Cache::forget(self::PUBLIC_POPULATION_STATISTICS);
        Cache::forget(self::PUBLIC_POPULATION_TREND);
    }

    public function invalidateMap(): void
    {
        Cache::forget(self::MAP_GEOJSON);
    }

    public function invalidateGallery(): void
    {
        Cache::forget(self::GALLERY);
    }

    public function invalidatePublications(): void
    {
        Cache::forget(self::PUBLICATIONS);
        Cache::forget(self::SEO_SITEMAP);
    }

    public function invalidateMediaDependencies(): void
    {
        $this->invalidateNews();
        $this->invalidateProfile();
        $this->invalidateOfficials();
        $this->invalidateGallery();
        $this->invalidateMap();
        $this->invalidatePublications();
    }

    private function newsDetailVersion(): int
    {
        $version = Cache::get(self::NEWS_DETAIL_VERSION);

        if (is_numeric($version)) {
            return max((int) $version, 1);
        }

        Cache::forever(self::NEWS_DETAIL_VERSION, 1);

        return 1;
    }

    private function bumpNewsDetailVersion(): void
    {
        Cache::forever(self::NEWS_DETAIL_VERSION, $this->newsDetailVersion() + 1);
    }
}
