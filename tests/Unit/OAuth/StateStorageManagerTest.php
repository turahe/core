<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\OAuth;

use Turahe\Core\OAuth\State\StateStorageManager;
use Turahe\Core\OAuth\State\StorageDrivers\Session;
use Turahe\Core\Tests\TestCase;
use TypeError;

class StateStorageManagerTest extends TestCase
{
    public function test_session_is_the_configured_driver(): void
    {
        config(['core.oauth.state.storage' => 'session']);

        $manager = new StateStorageManager($this->app);

        $this->assertSame('session', $manager->getDefaultDriver());
        $this->assertInstanceOf(Session::class, $manager->driver());
    }

    public function test_missing_storage_config_rejects_a_null_driver_name(): void
    {
        config(['core.oauth.state.storage' => null]);

        $this->expectException(TypeError::class);

        (new StateStorageManager($this->app))->getDefaultDriver();
    }
}
