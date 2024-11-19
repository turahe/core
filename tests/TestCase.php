<?php

namespace Turahe\Core\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Turahe\Core\Tests\Models\User;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
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

    protected function setUpDatabase()
    {
        $this->app['config']->set('auth.providers.users.model', User::class);

        $this->app['db']->connection()->getSchemaBuilder()->create('dummies', function ($table) {
            $table->ulid('id')->primary();
            $table->string('name');
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('organizations', function ($table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->ulid('id')->primary();
            $table->timestamps();
        });
    }
}
