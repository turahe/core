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

class ID extends Column
{
    /**
     * Initialize new ID instance.
     */
    public function __construct(?string $label = null, string $attribute = 'id')
    {
        parent::__construct($attribute, $label);

        $this->minWidth('120px')->width('120px');
    }
}
