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

namespace Modules\Core\VoIP\Events;

use Modules\Core\VoIP\Call;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class IncomingCallMissed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create new instance of IncomingCallMissed.
     */
    public function __construct(public Call $call)
    {
    }
}
