<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
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
        Gate::before(fn (User $user) => $user->isSuperAdmin() ? true : null);
        Paginator::useBootstrapThree();
        Gate::define('manage-content', fn (User $user) => $user->hasRole('admin_konten'));
        Gate::define('manage-data', fn (User $user) => $user->hasRole('admin_data'));
        Gate::define('manage-media', fn (User $user) => $user->hasRole('admin_konten', 'admin_data'));
        Gate::define('manage-users', fn (User $user) => false);
    }
}
