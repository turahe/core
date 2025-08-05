<?php

namespace Turahe\Core;

use Illuminate\Support\Facades\AliasLoader;
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

        // Register Google and Microsoft services as singletons
        $this->app->singleton('google', function ($app) {
            return new \Turahe\Core\Google\Client();
        });
        $this->app->singleton('msgraph', function ($app) {
            return new \Turahe\Core\Microsoft\Client();
        });
    }

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/core.php', 'core');

        // Register facades/aliases for Google and MsGraph
        AliasLoader::getInstance()->alias('Google', \Turahe\Core\Facades\Google::class);
        AliasLoader::getInstance()->alias('MsGraph', \Turahe\Core\Facades\MsGraph::class);

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
