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

namespace Turahe\Core\Microsoft\OAuth;

use Turahe\Core\OAuth\ResourceOwner;

class MicrosoftResourceOwner extends ResourceOwner
{
    /**
     * Get the resource owner email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->response['email'] ?? $this->response['userPrincipalName'];
    }
}
