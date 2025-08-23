<?php

namespace Turahe\Core;

use Illuminate\Support\ServiceProvider;

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
            \Turahe\Core\Contracts\TaxonomyRepositoryInterface::class,
            \Turahe\Core\Repositories\TaxonomyRepository::class
        );
        $this->app->bind(
            \Turahe\Core\Contracts\TagRepositoryInterface::class,
            \Turahe\Core\Repositories\TagRepository::class
        );

        // Register Google and Microsoft services as singletons
        // These services are registered as singletons to maintain state and connection
        // across multiple requests and avoid recreating OAuth clients unnecessarily
        $this->app->singleton('google', function ($app) {
            return new \Turahe\Core\Google\Client;
        });
        $this->app->singleton('msgraph', function ($app) {
            return new \Turahe\Core\Microsoft\Client;
        });

        // Register additional core services as singletons for better performance
        $this->app->singleton(\Turahe\Core\Services\Image\Image::class);
        $this->app->singleton(\Turahe\Core\Contracts\ImageSignatureInterface::class);
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
        if (class_exists(\Illuminate\Support\Facades\AliasLoader::class)) {
            \Illuminate\Support\Facades\AliasLoader::getInstance()->alias('Google', \Turahe\Core\Facades\Google::class);
            \Illuminate\Support\Facades\AliasLoader::getInstance()->alias('MsGraph', \Turahe\Core\Facades\MsGraph::class);
        }

        // Only load migrations and publish config for Laravel applications
        // This prevents issues when the package is used outside of Laravel
        if ($this->app instanceof \Illuminate\Foundation\Application) {
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
