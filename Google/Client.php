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

namespace Modules\Core\Google;

use Google\Client as GoogleClient;
use Modules\Core\OAuth\OAuthManager;
use Modules\Core\Google\Services\Labels;
use Modules\Core\Google\Services\History;
use Modules\Core\Google\Services\Message;
use Modules\Core\Google\Services\Calendar;
use Modules\Core\OAuth\AccessTokenProvider;

class Client
{
    /**
     * Google Client instance.
     */
    protected ?GoogleClient $client = null;

    /**
     * The OAuth email address to use to fetch the token.
     */
    protected static ?string $email = null;

    /**
     * Provide a connector for the access token.
     */
    public function connectUsing(string|AccessTokenProvider $connector): static
    {
        static::$email = is_string($connector) ? $connector : $connector->getEmail();

        // Reset the client so the next time can be retrieved with the new connector
        $this->client = null;

        return $this;
    }

    /**
     * Create new Labels instance.
     */
    public function labels(): Labels
    {
        return new Labels($this->getClient());
    }

    /**
     * Create new Message instance.
     */
    public function message(): Message
    {
        return new Message($this->getClient());
    }

    /**
     * Create new History instance.
     */
    public function history(): History
    {
        return new History($this->getClient());
    }

    /**
     * Create new Calendar instance.
     */
    public function calendar(): Calendar
    {
        return new Calendar($this->getClient());
    }

    /**
     * Get the Google client instance.
     */
    public function getClient(): GoogleClient
    {
        if ($this->client) {
            return $this->client;
        }

        $client = new GoogleClient;

        // Perhaps via revoke?
        if ($email = static::$email) {
            $client->setAccessToken([
                'access_token' => (new OAuthManager)->retrieveAccessToken('google', $email),
            ]);
        }

        return $this->client = $client;
    }

    /**
     * Revoke the current token.
     *
     * The access token to revoke or the current one that is set via the connectUsing method will be used.
     */
    public function revokeToken(?string $accessToken = null): void
    {
        $this->getClient()->revokeToken($accessToken);
    }
}
