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
        // Check if we're in a Docker environment with database services
        $hasDatabaseServices = $this->hasDatabaseServices();
        
        if ($hasDatabaseServices) {
            // Determine database type from environment
            $dbConnection = env('DB_CONNECTION', 'mysql');
            
            if ($dbConnection === 'pgsql') {
                // Use PostgreSQL for testing
                $app['config']->set('database.default', 'pgsql');
                $app['config']->set('database.connections.pgsql', [
                    'driver' => 'pgsql',
                    'host' => env('DB_HOST', '127.0.0.1'),
                    'port' => env('DB_PORT', 5432),
                    'database' => env('DB_DATABASE', 'turahe_core_testing'),
                    'username' => env('DB_USERNAME', 'turahe'),
                    'password' => env('DB_PASSWORD', 'postgres'),
                    'charset' => 'utf8',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'search_path' => 'public',
                    'sslmode' => 'prefer',
                ]);
            } else {
                // Use MySQL for testing (default)
                $app['config']->set('database.default', 'mysql');
                $app['config']->set('database.connections.mysql', [
                    'driver' => 'mysql',
                    'host' => env('DB_HOST', '127.0.0.1'),
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
            }
            
            // Set Redis configuration
            $app['config']->set('cache.default', 'redis');
            $app['config']->set('cache.stores.redis', [
                'driver' => 'redis',
                'connection' => 'default',
            ]);
            
            $app['config']->set('database.redis', [
                'client' => env('REDIS_CLIENT', 'phpredis'),
                'default' => [
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'password' => env('REDIS_PASSWORD', null),
                    'port' => env('REDIS_PORT', 6379),
                    'database' => env('REDIS_DB', 1),
                ],
            ]);
        } else {
            // Use SQLite for unit tests (no external services)
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
            
            // Use array cache for unit tests
            $app['config']->set('cache.default', 'array');
        }
        
        // Set core configuration from config/core.php
        $app['config']->set('core.table.use_timestamps', env('CORE_TABLE_USE_TIMESTAMPS', false));
        
        // Set userstamps configuration
        $app['config']->set('userstamps.users_table_column_type', env('USERSTAMPS_USERS_TABLE_COLUMN_TYPE', 'ulid'));
        
        $app['config']->set('app.key', env('APP_KEY', 'base64:12345678901234567890123456789012='));
        $app['config']->set('app.cipher', env('APP_CIPHER', 'AES-128-CBC'));
    }

    /**
     * Check if we're running in an environment with database services
     */
    protected function hasDatabaseServices(): bool
    {
        // Check if we're in a Docker container with database services
        if (env('DB_HOST') && env('DB_HOST') !== '127.0.0.1') {
            return true;
        }
        
        // Check if we can connect to the configured database
        try {
            $host = env('DB_HOST', '127.0.0.1');
            $port = env('DB_PORT', 3306);
            $connection = env('DB_CONNECTION', 'mysql');
            
            // Try to connect to the database
            $connection = @fsockopen($host, $port, $errno, $errstr, 5);
            if ($connection) {
                fclose($connection);
                return true;
            }
        } catch (\Exception $e) {
            // Connection failed, continue with SQLite
        }
        
        return false;
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
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Set userstamps configuration for testing
     */
    protected function setUserstampsConfig(string $type): void
    {
        config(['userstamps.users_table_column_type' => $type]);
    }
}
