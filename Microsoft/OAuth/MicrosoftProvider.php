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

namespace Modules\Core\Microsoft\OAuth;

use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class MicrosoftProvider extends GenericProvider
{
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct(array_merge($options, [
            'urlAuthorize'            => $this->getBaseAuthorizationUrl(),
            'urlAccessToken'          => $this->getBaseAccessTokenUrl([]),
            'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',
        ]), $collaborators);
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        $config = config('core.microsoft');

        return collect([
            $config['login_url_base'],
            $config['tenant_id'],
            $config['oauth2_path'],
        ])->map(fn ($part) => trim($part, '/'))->implode('/').'/authorize';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        $config = config('core.microsoft');

        return collect([
            $config['login_url_base'],
            $config['tenant_id'],
            $config['oauth2_path'],
        ])->map(fn ($part) => trim($part, '/'))->implode('/').'/token';
    }

    /**
     * Generate a user object from a successful user details request.
     *
     *
     * @return \Modules\Core\Microsoft\OAuth\MicrosoftResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new MicrosoftResourceOwner($response);
    }

    /**
     * {@inheritdoc}
     *
     * @see https://docs.microsoft.com/en-us/graph/auth-v2-user#authorization-request
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * Checks a provider response for errors.
     *
     * @param  array|string  $data Parsed response data
     * @return void
     *
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException((isset($data['error']['message']) ? $data['error']['message'] : $response->getReasonPhrase()), $response->getStatusCode(), $response);
        }
    }
}
