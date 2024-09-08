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

namespace Turahe\Core\OAuth;

use League\OAuth2\Client\Grant\RefreshToken;
use Turahe\Core\Google\OAuth\GoogleProvider;
use Turahe\Core\Microsoft\OAuth\MicrosoftProvider;
use Turahe\Core\Models\OAuthAccount;
use Turahe\Core\OAuth\Events\OAuthAccountConnected;

class OAuthManager
{
    /**
     * @var null|int
     */
    protected $userId;

    /**
     * Set the application user the token is related to
     *
     * @param  int  $userId
     * @return static
     */
    public function forUser($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Generates random state
     *
     * @param  int  $length
     * @return string
     */
    public function generateRandomState($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Connect OAuth account
     *
     * @param  string  $type
     * @param  string  $code
     * @return \Turahe\Core\Models\OAuthAccount
     */
    public function connect($type, $code)
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
     * @param  string  $type
     * @param  string  $email
     * @return string
     */
    public function retrieveAccessToken($type, $email)
    {
        $account = $this->getAccount($type, $email);

        // Check if token is expired
        // Get current time + 5 minutes (to allow for time differences)
        if ($account->expires <= time() + 300) {
            // Token is expired (or very close to it) so let's refresh
            $newToken = $this->refreshToken($type, $account->refresh_token);

            return tap($newToken->getToken(), function ($refreshedToken) use ($newToken, $account) {
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
     *
     * @param  string  $type
     * @return \Turahe\Core\Contracts\OAuth\Provider
     */
    public function createProvider($type)
    {
        return $this->{'create'.ucfirst($type).'Provider'}();
    }

    /**
     * Create Google OAuth Provider
     *
     * @return \Turahe\Core\Google\OAuthProvider
     */
    public function createGoogleProvider()
    {
        $redirectUrl = config('core.google.redirect_url');

        return new GoogleProvider([
            'clientId' => config('core.google.client_id'),
            'clientSecret' => config('core.google.client_secret'),
            'redirectUri' => $redirectUrl ?: url(config('core.google.redirect_uri')),
            'accessType' => config('core.google.access_type'),
            'scopes' => config('core.google.scopes'),
        ]);
    }

    /**
     * Create Microsoft OAuth Provider
     *
     * @return \Turahe\Core\Google\OAuthProvider
     */
    public function createMicrosoftProvider()
    {
        $redirectUrl = config('core.microsoft.redirect_url');

        return new MicrosoftProvider([
            'clientId' => config('core.microsoft.client_id'),
            'clientSecret' => config('core.microsoft.client_secret'),
            'redirectUri' => $redirectUrl ?: url(config('core.microsoft.redirect_uri')),
            'scopes' => config('core.microsoft.scopes'),
        ]);
    }

    /**
     * Refresh the token based on a given refresh token
     *
     * @param  string  $type
     * @param  string  $refreshToken
     * @return \League\OAuth2\Client\Token\AccessTokenInterface
     */
    public function refreshToken($type, $refreshToken)
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
     *
     * @param  string  $type
     * @param  string  $email
     * @return \Turahe\Core\Models\OAuthAccount
     */
    public function getAccount($type, $email)
    {
        return OAuthAccount::where('type', $type)->where('email', $email)->first();
    }

    /**
     * Store account and it's tokens in the database
     *
     * @param  string  $type
     * @param  \League\OAuth2\Client\Token\AccessTokenInterface  $accessToken
     * @param  \Turahe\Core\OAuth\User  $user
     * @return \Turahe\Core\Models\OAuthAccount
     */
    protected function storeAccount($type, $accessToken, $user)
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
