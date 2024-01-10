<?php

namespace App\Providers;

use App\Actions\Photos\ExtractLocationFromPhotoAction;
use App\Actions\Photos\ExtractsLocationFromPhoto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);

        Model::unguard();

        Model::shouldBeStrict();

        $this->app->bind(ExtractsLocationFromPhoto::class, ExtractLocationFromPhotoAction::class);
    }
}
