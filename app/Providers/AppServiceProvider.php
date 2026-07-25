<?php

namespace App\Providers;

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
use App\Models\Publication;
use App\Models\PublicationAttachment;
use App\Models\Resident;
use App\Models\ResidentEvent;
use App\Models\Setting;
use App\Models\StatisticDataset;
use App\Models\StatisticValue;
use App\Models\User;
use App\Models\VillageProfileSection;
use App\Observers\PublicContentCacheObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([
            Setting::class,
            VillageProfileSection::class,
            Official::class,
            News::class,
            NewsCategory::class,
            Resident::class,
            FamilyCard::class,
            Household::class,
            PopulationArea::class,
            ResidentEvent::class,
            StatisticDataset::class,
            StatisticValue::class,
            MapLayer::class,
            MapFeature::class,
            Gallery::class,
            GalleryItem::class,
            Publication::class,
            PublicationAttachment::class,
            Media::class,
        ] as $model) {
            $model::observe(PublicContentCacheObserver::class);
        }

        Gate::before(fn (User $user) => $user->isSuperAdmin() ? true : null);
        Paginator::useBootstrapThree();
        Gate::define('manage-content', fn (User $user) => $user->hasRole('admin_konten'));
        Gate::define('manage-data', fn (User $user) => $user->hasRole('admin_data'));
        Gate::define('manage-media', fn (User $user) => $user->hasRole('admin_konten', 'admin_data'));
        Gate::define('manage-users', fn (User $user) => false);
    }
}
