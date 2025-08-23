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
        $app['config']->set('cache.stores.array', [
            'driver' => 'array',
            'serialize' => false,
        ]);
        
        // Set session driver to array for testing
        $app['config']->set('session.driver', 'array');
        
        // Set queue connection to sync for testing
        $app['config']->set('queue.default', 'sync');
        
        // Set core configuration from config/core.php
        $app['config']->set('core.table.use_timestamps', false);
        
        // Set userstamps configuration
        $app['config']->set('userstamps.users_table_column_type', 'ulid');
        
        $app['config']->set('app.key', 'base64:MFOsOH9RomiI2LRdgP4hIeoQJ5nyBhdABdH77UY2zi8=');
        $app['config']->set('app.cipher', 'AES-256-CBC');
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
