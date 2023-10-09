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

namespace Modules\Core\Fields;

use Modules\Core\Table\Column;

class Email extends Text
{
    /**
     * Input type
     */
    public string $inputType = 'email';

    /**
     * Boot the field
     *
     * @return void
     */
    public function boot()
    {
        $this->rules(['email', 'nullable'])->prependIcon('Mail')
            ->tapIndexColumn(function (Column $column) {
                $column->useComponent('table-data-email-column');
            })->provideSampleValueUsing(fn () => uniqid().'@example.com');
    }
}
