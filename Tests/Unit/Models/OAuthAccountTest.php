<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
 *
 *
 */

namespace Turahe\Core\Tests\Unit\Models;

use Illuminate\Support\Facades\Crypt;
use Turahe\Core\Models\OAuthAccount;
use Turahe\Core\OAuth\AccessTokenProvider;
use Turahe\Core\Tests\TestCase;

class OAuthAccountTest extends TestCase
{
    public function test_it_encrypts_the_oauth_account_access_token()
    {
        Crypt::shouldReceive('encrypt')->once()
            ->with('token', false)
            ->andReturnArg(0);

        new OAuthAccount(['access_token' => 'token']);
    }

    public function test_it_decrypts_the_oauth_account_access_token()
    {
        $account = new OAuthAccount(['access_token' => 'token']);

        Crypt::shouldReceive('decrypt')->once()
            ->andReturn('token');

        $this->assertEquals('token', $account->access_token);
    }

    public function test_oauth_account_has_access_token_provider()
    {
        $account = new OAuthAccount(['access_token' => 'token', 'email' => 'john@example.com']);

        $this->assertInstanceOf(AccessTokenProvider::class, $account->tokenProvider());
    }
}
