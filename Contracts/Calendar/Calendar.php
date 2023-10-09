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

namespace Modules\Core\Contracts\Calendar;

interface Calendar
{
    /**
     * Get the calendar ID.
     */
    public function getId(): int|string;

    /**
     * Get the calendar title.
     */
    public function getTitle(): string;

    /**
     * Check whether the calendar is default.
     */
    public function isDefault(): bool;
}
