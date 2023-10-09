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

namespace Modules\Core\Google\Services;

use Google\Client;

class Labels extends Service
{
    /**
     * Initialize new Labels service instance.
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, \Google\Service\Gmail::class);
    }

    /**
     * List all available user labels
     *
     * @return array
     */
    public function list()
    {
        /** @var \Google\Service\Gmail */
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
     * @return \Google\Service\Gmail\Label
     */
    public function get($id)
    {
        /** @var \Google\Service\Gmail */
        $service = $this->service;

        return $service->users_labels->get('me', $id);
    }
}
