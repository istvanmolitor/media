<?php

namespace Molitor\Media\Providers;

use Illuminate\Support\ServiceProvider;
use Molitor\Media\Repositories\MediaFileRepository;
use Molitor\Media\Repositories\MediaFileRepositoryInterface;
use Molitor\Media\Repositories\MediaFolderRepository;
use Molitor\Media\Repositories\MediaFolderRepositoryInterface;

class MediaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load API routes with /api prefix
        $this->app->make(\Illuminate\Routing\Router::class)
            ->prefix('api')
            ->group(__DIR__ . '/../routes/api.php');
    }

    public function register()
    {
        $this->app->bind(MediaFileRepositoryInterface::class, MediaFileRepository::class);
        $this->app->bind(MediaFolderRepositoryInterface::class, MediaFolderRepository::class);
    }
}
