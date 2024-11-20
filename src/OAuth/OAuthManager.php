<?php

declare(strict_types=1);
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

namespace Turahe\Core\OAuth;

use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Turahe\Core\Google\OAuth\GoogleProvider;
use Turahe\Core\Microsoft\OAuth\MicrosoftProvider;
use Turahe\Core\Models\OAuthAccount;
use Turahe\Core\Models\User;
use Turahe\Core\OAuth\Events\OAuthAccountConnected;

class OAuthManager
{
    /**
     * @var null|int
     */
    protected $userId;

    /**
     * Set the Turahe\Corelication user the token is related to
     */
    public function forUser(string|int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Generates random state
     *
     * @throws \Random\RandomException
     */
    public function generateRandomState(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Connect OAuth account
     */
    public function connect(string $type, string $code): OAuthAccount
    {
        $provider = $this->createProvider($type);

        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

        $user = $provider->getResourceOwner($accessToken);
        $account = $this->storeAccount($type, $accessToken, $user);

        event(new OAuthAccountConnected($account));

        return $account;
    }

    /**
     * Get the access token by email
     *
     * @throws EmptyRefreshTokenException
     */
    public function retrieveAccessToken(string $type, string $email): \Illuminate\Support\HigherOrderTTurahe\Coreroxy
    {
        $account = $this->getAccount($type, $email);

        // Check if token is expired
        // Get current time + 5 minutes (to allow for time differences)
        if ($account->expires <= time() + 300) {
            // Token is expired (or very close to it) so let's refresh
            $newToken = $this->refreshToken($type, $account->refresh_token);

            return tap($newToken->getToken(), function ($refreshedToken) use ($newToken, $account): void {
                $account->fill([
                    'access_token' => $refreshedToken,
                    'expires' => $newToken->getExpires(),
                ])->save();
            });
        }

        // Token is still valid, just return it
        return $account->access_token;
    }

    /**
     * Create OAuth Provider
     * type is google or microsoft
     */
    public function createProvider(string $type)
    {
        return $this->{'create'.ucfirst($type).'Provider'}();
    }

    /**
     * Create Google OAuth Provider
     */
    public function createGoogleProvider(): GoogleProvider
    {
        $redirectUrl = config('services.google.redirect_url');

        return new GoogleProvider([
            'clientId' => config('services.google.client_id'),
            'clientSecret' => config('services.google.client_secret'),
            'redirectUri' => $redirectUrl ?: url(config('services.google.redirect_uri')),
            'accessType' => config('services.google.access_type'),
            'scopes' => config('services.google.scopes'),
        ]);
    }

    /**
     * Create Microsoft OAuth Provider
     */
    public function createMicrosoftProvider(): MicrosoftProvider
    {
        $redirectUrl = config('services.microsoft.redirect_url');

        return new MicrosoftProvider([
            'clientId' => config('services.microsoft.client_id'),
            'clientSecret' => config('services.microsoft.client_secret'),
            'redirectUri' => $redirectUrl ?: url(config('services.microsoft.redirect_uri')),
            'scopes' => config('services.microsoft.scopes'),
        ]);
    }

    /**
     * Refresh the token based on a given refresh token
     *
     * @throws EmptyRefreshTokenException
     */
    public function refreshToken(string $type, string $refreshToken): AccessTokenInterface
    {
        if (empty($refreshToken)) {
            throw new EmptyRefreshTokenException;
        }

        return $this->createProvider($type)->getAccessToken(new RefreshToken, [
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * Get the access token account
     */
    public function getAccount(string $type, string $email): OAuthAccount
    {
        return OAuthAccount::where('type', $type)->where('email', $email)->first();
    }

    /**
     * Store account and it's tokens in the database
     */
    protected function storeAccount(string $type, AccessTokenInterface $accessToken, User $user): OAuthAccount
    {
        $data = [
            'email' => $user->getEmail(),
            'access_token' => $accessToken->getToken(),
            'expires' => $accessToken->getExpires(),
            'oauth_user_id' => $user->getId(),
            'requires_auth' => false,
        ];

        // E.q. for Google, only it's returned on the first connection
        if ($refreshToken = $accessToken->getRefreshToken()) {
            $data['refresh_token'] = $refreshToken;
        }

        if ($this->userId) {
            $data['user_id'] = $this->userId;
        }

        return OAuthAccount::updateOrCreate(['email' => $data['email'], 'type' => $type], $data);
    }
}
