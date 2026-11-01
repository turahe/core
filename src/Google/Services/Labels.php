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
use Google\Service\Gmail\Label;

class Labels extends Service
{
    /**
     * Initialize new Labels service instance.
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, Gmail::class);
    }

    /**
     * List all available user labels
     *
     * @return array
     */
    public function list()
    {
        /** @var Gmail */
        $service = $this->service;

        $labels = [];
        $labelsResponse = $service->users_labels->listUsersLabels('me');

        if ($labelsResponse->getLabels()) {
            $labels = array_merge($labels, $labelsResponse->getLabels());
        }

        return $labels;
    }

    /**
     * Get user label by id
     *
     * @param  string  $id
     * @return Label
     */
    public function get($id)
    {
        /** @var Gmail */
        $service = $this->service;

        return $service->users_labels->get('me', $id);
    }
}
