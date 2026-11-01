<?php

namespace Turahe\Core;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Turahe\Core\Contracts\ImageSignatureInterface;
use Turahe\Core\Contracts\TagRepositoryInterface;
use Turahe\Core\Contracts\TaxonomyRepositoryInterface;
use Turahe\Core\Facades\Google;
use Turahe\Core\Facades\MsGraph;
use Turahe\Core\Google\Client;
use Turahe\Core\Repositories\TagRepository;
use Turahe\Core\Repositories\TaxonomyRepository;
use Turahe\Core\Services\Image\Image;

/**
 * Core Service Provider for Turahe Core Package
 *
 * This service provider handles the registration and bootstrapping of core services,
 * including repository bindings, OAuth clients, facades, and configuration management.
 *
 * @author Turahe Team
 */
class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Binds repository interfaces to their concrete implementations and registers
     * OAuth service clients as singletons for dependency injection.
     */
    public function register()
    {
        // Bind repository interfaces to concrete implementations
        $this->app->bind(
            TaxonomyRepositoryInterface::class,
            TaxonomyRepository::class
        );
        $this->app->bind(
            TagRepositoryInterface::class,
            TagRepository::class
        );

        // Register Google and Microsoft services as singletons
        // These services are registered as singletons to maintain state and connection
        // across multiple requests and avoid recreating OAuth clients unnecessarily
        $this->app->singleton('google', function ($app) {
            return new Client;
        });
        $this->app->singleton('msgraph', function ($app) {
            return new Microsoft\Client;
        });

        // Register additional core services as singletons for better performance
        $this->app->singleton(Image::class);
        $this->app->singleton(ImageSignatureInterface::class);
    }

    /**
     * Boot the application events.
     *
     * This method is called after all other service providers have been registered,
     * allowing us to safely use other services and perform bootstrapping tasks.
     */
    public function boot(): void
    {
        // Merge package configuration with application config
        // This allows users to override default settings in their config/core.php
        $this->mergeConfigFrom(__DIR__.'/../config/core.php', 'core');

        // Register facades/aliases for Google and MsGraph services
        // Only register if AliasLoader is available (Laravel 5.8+)
        if (class_exists(AliasLoader::class)) {
            AliasLoader::getInstance()->alias('Google', Google::class);
            AliasLoader::getInstance()->alias('MsGraph', MsGraph::class);
        }

        // Only load migrations and publish config for Laravel applications
        // This prevents issues when the package is used outside of Laravel
        if ($this->app instanceof Application) {
            // Load package migrations from the database/migrations directory
            $databasePath = __DIR__.'/../database/migrations';
            $this->loadMigrationsFrom($databasePath);

            // Publish configuration file to allow users to customize settings
            // Users can run: php artisan vendor:publish --tag=config
            $this->publishes(
                [
                    __DIR__.'/../config/core.php' => config_path('core.php'),
                ],
                'config'
            );
        }
    }
}
