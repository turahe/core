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

namespace Turahe\Core\Calendar;

use Turahe\Core\AbstractMask;
use Turahe\Core\Contracts\Calendar\Calendar as CalendarInterface;

abstract class AbstractCalendar extends AbstractMask implements CalendarInterface
{
    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * toArray
     */
    public function toArray(): array
    {
        return [
            'id'         => $this->getId(),
            'title'      => $this->getTitle(),
            'is_default' => $this->isDefault(),
        ];
    }
}
