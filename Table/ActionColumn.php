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

class ActionColumn extends Column
{
    /**
     * This column is not sortable
     */
    public bool $sortable = false;

    /**
     * Indicates whether the column can be customized.
     */
    public bool $customizeable = false;

    /**
     * Initialize new ActionColumn instance.
     */
    public function __construct(?string $label = null, string $attribute = 'actions')
    {
        // Set the attribute to null to prevent showing on re-order table options
        parent::__construct($attribute, $label);

        $this->width('150px');
    }
}
