<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\OAuth;

use Turahe\Core\OAuth\State\StorageDrivers\Session;
use Turahe\Core\Tests\TestCase;

class SessionStateStorageTest extends TestCase
{
    public function test_get_returns_null_when_nothing_is_stored(): void
    {
        $storage = new Session;

        $this->assertFalse($storage->has());
        $this->assertNull($storage->get());
    }

    public function test_put_then_get_returns_the_state(): void
    {
        $storage = new Session;

        $storage->put('abc');

        $this->assertTrue($storage->has());
        $this->assertSame('abc', $storage->get());
    }

    public function test_forget_removes_the_state(): void
    {
        $storage = new Session;
        $storage->put('abc');

        $storage->forget();

        $this->assertFalse($storage->has());
        $this->assertNull($storage->get());
    }
}
