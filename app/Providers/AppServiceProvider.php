<?php

namespace App\Providers;

use App\Models\FamilyCard;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Household;
use App\Models\LetterApplication;
use App\Models\LetterService;
use App\Models\MapFeature;
use App\Models\MapLayer;
use App\Models\Media;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Official;
use App\Models\PopulationArea;
use App\Models\PopulationGroup;
use App\Models\PopulationGroupMember;
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
use App\Policies\AdminResourcePolicy;
use App\Policies\CmsResourcePolicy;
use App\Policies\DataResourcePolicy;
use App\Policies\FamilyCardPolicy;
use App\Policies\HouseholdPolicy;
use App\Policies\LetterApplicationPolicy;
use App\Policies\LetterServicePolicy;
use App\Policies\OfficialPolicy;
use App\Policies\PopulationGroupMemberPolicy;
use App\Policies\PopulationGroupPolicy;
use App\Policies\ResidentPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        RateLimiter::for('admin-login', function (Request $request) {
            $email = Str::lower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
        RateLimiter::for('letter-application', fn (Request $request) => [
            Limit::perMinute(3)->by($request->ip()),
            Limit::perDay(10)->by($request->ip()),
        ]);
        RateLimiter::for('letter-application-update', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('letter-tracking', function (Request $request) {
            $number = Str::upper(trim((string) $request->input('application_number')));

            return [Limit::perMinute(5)->by($request->ip()), Limit::perMinute(5)->by($number.'|'.$request->ip())];
        });

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
        Gate::define('manage-letter-applications', fn (User $user) => $user->hasRole('admin_data'));
        Gate::define('manage-letter-services', fn (User $user) => false);

        foreach (config('admin.resources', []) as $resource) {
            $policy = match ($resource['ability']) {
                'manage-data' => DataResourcePolicy::class,
                'manage-users' => AdminResourcePolicy::class,
                default => CmsResourcePolicy::class,
            };
            Gate::policy($resource['model'], $policy);
        }

        Gate::policy(Official::class, OfficialPolicy::class);
        Gate::policy(Resident::class, ResidentPolicy::class);
        Gate::policy(FamilyCard::class, FamilyCardPolicy::class);
        Gate::policy(Household::class, HouseholdPolicy::class);
        Gate::policy(PopulationGroup::class, PopulationGroupPolicy::class);
        Gate::policy(PopulationGroupMember::class, PopulationGroupMemberPolicy::class);
        Gate::policy(LetterApplication::class, LetterApplicationPolicy::class);
        Gate::policy(LetterService::class, LetterServicePolicy::class);
    }
}
