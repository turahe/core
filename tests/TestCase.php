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
        // Use SQLite for all testing (in-memory)
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        
        // Use array cache for all testing
        $app['config']->set('cache.default', 'array');
        
        // Set core configuration from config/core.php
        $app['config']->set('core.table.use_timestamps', env('CORE_TABLE_USE_TIMESTAMPS', false));
        
        // Set userstamps configuration
        $app['config']->set('userstamps.users_table_column_type', 'ulid');
        
        $app['config']->set('app.key', env('APP_KEY', 'base64:12345678901234567890123456789012='));
        $app['config']->set('app.cipher', env('APP_CIPHER', 'AES-128-CBC'));
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
