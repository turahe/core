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

class AccessTokenProvider
{
    /**
     * Initialize the acess token provider class
     */
    public function __construct(protected string $token, protected string $email)
    {
    }

    /**
     * Get the access token
     */
    public function getAccessToken(): string
    {
        return $this->token;
    }

    /**
     * Get the token email adress
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
