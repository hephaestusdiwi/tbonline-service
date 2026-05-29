<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\HomepageSectionService;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(HomepageSectionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Content::class, ContentPolicy::class);
    }
}
