<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\OAuth;

use Error;
use Turahe\Core\OAuth\EmptyRefreshTokenException;
use Turahe\Core\OAuth\OAuthManager;
use Turahe\Core\Tests\TestCase;

class OAuthManagerTest extends TestCase
{
    public function test_generate_random_state_returns_hex_of_the_requested_length(): void
    {
        $manager = new OAuthManager;

        $state = $manager->generateRandomState();
        $short = $manager->generateRandomState(8);

        $this->assertSame(32, strlen($state));
        $this->assertSame(8, strlen($short));
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $state);
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $short);
    }

    public function test_for_user_returns_the_same_manager(): void
    {
        $manager = new OAuthManager;

        $this->assertSame($manager, $manager->forUser(5));
    }

    public function test_refresh_token_rejects_an_empty_token(): void
    {
        $this->expectException(EmptyRefreshTokenException::class);

        (new OAuthManager)->refreshToken('google', '');
    }

    public function test_create_provider_rejects_an_unknown_type(): void
    {
        $this->expectException(Error::class);

        (new OAuthManager)->createProvider('unknown');
    }
}
