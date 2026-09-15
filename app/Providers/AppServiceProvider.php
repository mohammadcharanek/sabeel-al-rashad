<?php

namespace App\Providers;

use App\Models\EducationalStage;
use App\Models\Event;
use App\Models\NewsPost;
use App\Models\User;
use App\Policies\CmsContentPolicy;
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
        Gate::define('manage-settings', fn (User $user): bool => $user->is_admin);

        foreach ([NewsPost::class, Event::class, EducationalStage::class] as $model) {
            Gate::policy($model, CmsContentPolicy::class);
        }
    }
}
