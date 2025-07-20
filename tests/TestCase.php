<?php

namespace Turahe\Core\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Turahe\Core\Tests\Models\User;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->loadMigrationsFrom(__DIR__.'./../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Spatie\EloquentSortable\EloquentSortableServiceProvider::class,
            \Turahe\UserStamps\UserStampsServiceProvider::class,
            \Turahe\Core\CoreServiceProvider::class,
        ];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // Use MySQL for testing (Docker environment)
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'mysql'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'turahe_core_testing'),
            'username' => env('DB_USERNAME', 'turahe'),
            'password' => env('DB_PASSWORD', 'turahe123'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
        
        // Keep SQLite as fallback for local testing
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        
        // Set core configuration from config/core.php
        $app['config']->set('core.table.use_timestamps', env('CORE_TABLE_USE_TIMESTAMPS', false));
        
        // Set userstamps configuration
        $app['config']->set('userstamps.users_table_column_type', env('USERSTAMPS_USERS_TABLE_COLUMN_TYPE', 'ulid'));
        
        // Set Redis configuration
        $app['config']->set('cache.default', 'redis');
        $app['config']->set('cache.stores.redis', [
            'driver' => 'redis',
            'connection' => 'default',
        ]);
        
        $app['config']->set('database.redis', [
            'client' => env('REDIS_CLIENT', 'phpredis'),
            'default' => [
                'host' => env('REDIS_HOST', 'redis'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', 6379),
                'database' => env('REDIS_DB', 1),
            ],
        ]);
        
        $app['config']->set('app.key', env('APP_KEY', 'base64:MFOsOH9RomiI2LRdgP4hIeoQJ5nyBhdABdH77UY2zi8='));
    }

    protected function setUpDatabase()
    {
        $this->app['config']->set('auth.providers.users.model', User::class);

        $this->app['db']->connection()->getSchemaBuilder()->create('dummies', function ($table) {
            $table->ulid('id')->primary();
            $table->string('name');
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }
}
