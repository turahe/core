<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Feature;

use Illuminate\Support\Facades\Crypt;
use Turahe\Core\Models\OAuthAccount;
use Turahe\Core\OAuth\AccessTokenProvider;
use Turahe\Core\Tests\TestCase;

class OAuthAccountRepositoryTest extends TestCase
{
    public function test_can_encrypts_the_oauth_account_access_token(): void
    {
        Crypt::shouldReceive('encrypt')->once()
            ->with('token', false)
            ->andReturnArg(0);

        new OAuthAccount(['access_token' => 'token']);
    }

    public function test_can_decrypts_the_oauth_account_access_token(): void
    {
        $account = new OAuthAccount(['access_token' => 'token']);

        Crypt::shouldReceive('decrypt')->once()
            ->andReturn('token');

        $this->assertEquals('token', $account->access_token);
    }

    public function test_can_oauth_account_has_access_token_provider(): void
    {
        $account = new OAuthAccount(['access_token' => 'token', 'email' => 'john@example.com']);

        $this->assertInstanceOf(AccessTokenProvider::class, $account->tokenProvider());
    }
}
