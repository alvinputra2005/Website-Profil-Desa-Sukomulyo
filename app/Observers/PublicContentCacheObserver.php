<?php

namespace App\Observers;

use App\Models\FamilyCard;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Household;
use App\Models\MapFeature;
use App\Models\MapLayer;
use App\Models\Media;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Official;
use App\Models\PopulationArea;
use App\Models\PopulationYearlySnapshot;
use App\Models\Publication;
use App\Models\PublicationAttachment;
use App\Models\Resident;
use App\Models\ResidentEvent;
use App\Models\Setting;
use App\Models\StatisticCategory;
use App\Models\StatisticDataset;
use App\Models\StatisticImport;
use App\Models\StatisticRow;
use App\Models\StatisticValue;
use App\Models\VillageProfileSection;
use App\Services\SiteCache;
use Illuminate\Database\Eloquent\Model;

class PublicContentCacheObserver
{
    public function __construct(private readonly SiteCache $cache) {}

    public function saved(Model $model): void
    {
        $this->invalidate($model);
    }

    public function deleted(Model $model): void
    {
        $this->invalidate($model);
    }

    public function restored(Model $model): void
    {
        $this->invalidate($model);
    }

    public function forceDeleted(Model $model): void
    {
        $this->invalidate($model);
    }

    private function invalidate(Model $model): void
    {
        match (true) {
            $model instanceof Setting => $this->cache->invalidateSettings(),
            $model instanceof VillageProfileSection => $this->cache->invalidateProfile(),
            $model instanceof Official => $this->cache->invalidateOfficials(),
            $model instanceof News, $model instanceof NewsCategory => $this->cache->invalidateNews(),
            $model instanceof Resident,
            $model instanceof FamilyCard,
            $model instanceof Household,
            $model instanceof PopulationArea,
            $model instanceof PopulationYearlySnapshot,
            $model instanceof ResidentEvent,
            $model instanceof StatisticCategory,
            $model instanceof StatisticDataset,
            $model instanceof StatisticImport,
            $model instanceof StatisticRow,
            $model instanceof StatisticValue => $this->cache->invalidatePopulationStatistics(),
            $model instanceof MapLayer, $model instanceof MapFeature => $this->cache->invalidateMap(),
            $model instanceof Gallery, $model instanceof GalleryItem => $this->cache->invalidateGallery(),
            $model instanceof Publication,
            $model instanceof PublicationAttachment => $this->cache->invalidatePublications(),
            $model instanceof Media => $this->cache->invalidateMediaDependencies(),
            default => null,
        };
    }
}
