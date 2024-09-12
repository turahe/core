<?php

namespace Turahe\Core\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Turahe\Core\Tests\Concerns\TestsCustomFields;
use Turahe\Core\Tests\Concerns\TestsImportAndExport;
use Turahe\Core\Tests\Models\User;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use TestsCustomFields, TestsImportAndExport;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            \Turahe\Core\Providers\CoreServiceProvider::class,
        ];
    }

    protected function setUpDatabase()
    {
        Config::set('auth.providers.users.model', User::class);

        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();

            $table->timestamps();
        });
    }
}
