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

namespace Turahe\Core\Google\Services;

use Google\Client;
use Google\Service\Gmail;

class History extends Service
{
    /**
     * Initialize new History service instance.
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, Gmail::class);
    }

    /**
     * https://developers.google.com/gmail/api/v1/reference/users/history/list
     *
     * Get the Gmail account history
     *
     * @param  array  $params  Additional params for the request
     * @return Gmail\History
     */
    public function get($params = [])
    {
        /** @var Gmail\History */
        $service = $this->service;

        return $service->users_history->listUsersHistory('me', $params);
    }
}
