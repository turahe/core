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

namespace Turahe\Core\Table;

use Turahe\Core\Facades\Format;

class DateTimeColumn extends Column
{
    /**
     * Initialize new DateTimeColumn instance.
     */
    public function __construct(...$params)
    {
        parent::__construct(...$params);

        $this->displayAs(fn ($model) => Format::dateTime($model->{$this->attribute}));
    }
}
