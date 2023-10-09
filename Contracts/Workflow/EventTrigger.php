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

namespace Modules\Core\Contracts\Workflow;

interface EventTrigger
{
    /**
     * The event name the trigger should be triggered
     */
    public static function event(): string;
}
