<?php

namespace Turahe\Core;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            \Turahe\Core\Contracts\TaxonomyRepositoryInterface::class,
            \Turahe\Core\Repositories\TaxonomyRepository::class
        );
        $this->app->bind(
            \Turahe\Core\Contracts\TagRepositoryInterface::class,
            \Turahe\Core\Repositories\TagRepository::class
        );
    }

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/core.php', 'core');

        if ($this->app instanceof \Illuminate\Foundation\Application) {
            $databasePath = __DIR__.'/../database/migrations';
            $this->loadMigrationsFrom($databasePath);

            $this->publishes(
                [
                    __DIR__.'/../config/core.php' => config_path('core.php'),
                ],
                'config'
            );
        }
    }
}
