<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\OAuth;

use Illuminate\Foundation\Application;
use InvalidArgumentException;
use Turahe\Core\OAuth\State\StateStorageManager;
use Turahe\Core\OAuth\State\StorageDrivers\Session;
use Turahe\Core\Tests\TestCase;

class StateStorageManagerTest extends TestCase
{
    public function test_session_is_the_configured_driver(): void
    {
        config(['core.oauth.state.storage' => 'session']);

        $app = $this->app;
        $this->assertInstanceOf(Application::class, $app);

        $manager = new StateStorageManager($app);

        $this->assertSame('session', $manager->getDefaultDriver());
        $this->assertInstanceOf(Session::class, $manager->driver());
    }

    public function test_missing_storage_config_rejects_a_null_driver_name(): void
    {
        config(['core.oauth.state.storage' => null]);

        $app = $this->app;
        $this->assertInstanceOf(Application::class, $app);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('OAuth state storage driver is not configured.');

        (new StateStorageManager($app))->getDefaultDriver();
    }
}
