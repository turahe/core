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

namespace Modules\Core\Table;

use Modules\Core\Facades\Format;

class DateColumn extends Column
{
    /**
     * Initialize new DateColumn instance.
     */
    public function __construct(...$params)
    {
        parent::__construct(...$params);

        $this->displayAs(fn ($model) => Format::date($model->{$this->attribute}));
    }
}
