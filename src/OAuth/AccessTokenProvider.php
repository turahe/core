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

class AccessTokenProvider
{
    /**
     * Initialize the access token provider class
     */
    public function __construct(protected string $token, protected string $email) {}

    /**
     * Get the access token
     */
    public function getAccessToken(): string
    {
        return $this->token;
    }

    /**
     * Get the token email address
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
