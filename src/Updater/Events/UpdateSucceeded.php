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

namespace Turahe\Core\Updater\Events;

use Turahe\Core\Updater\Release;

class UpdateSucceeded
{
    /**
     * Initialize new UpdateSucceeded instance.
     */
    public function __construct(protected Release $release)
    {
    }

    /**
     * Get the new version.
     */
    public function getVersionUpdatedTo(): string
    {
        return $this->release->getVersion();
    }
}
