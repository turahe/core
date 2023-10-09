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

namespace Modules\Core\VoIP;

use Illuminate\Support\Manager;
use Modules\Core\VoIP\Clients\Twilio;
use Modules\Core\Contracts\VoIP\ReceivesEvents;

class VoIPManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->container['config']['core.voip.client'];
    }

    /**
     * Create Twilio VoIP driver
     *
     * @return \Modules\Core\VoIP\Clients\Twilio
     */
    public function createTwilioDriver()
    {
        return new Twilio($this->container['config']['core.services.twilio']);
    }

    /**
     * Check whether the driver receives events
     *
     * @param  string|null  $driver
     * @return bool
     */
    public function shouldReceivesEvents($driver = null)
    {
        return $this->driver($driver) instanceof ReceivesEvents;
    }
}
