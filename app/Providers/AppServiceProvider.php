<?php

namespace App\Providers;

use App\Actions\Photos\ExtractExifFromPhotoAction;
use App\Actions\Photos\ExtractsExifFromPhoto;
use App\Actions\Photos\SuggestPhotoTagsAction;
use App\Actions\Photos\SuggestsPhotoTags;
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

        $this->app->bind(ExtractsExifFromPhoto::class, ExtractExifFromPhotoAction::class);
        $this->app->bind(SuggestsPhotoTags::class, SuggestPhotoTagsAction::class);
    }
}
