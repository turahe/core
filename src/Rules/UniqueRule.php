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

namespace Turahe\Core\Rules;

use Illuminate\Validation\Rules\Unique;
use Turahe\Core\Makeable;

class UniqueRule extends Unique
{
    use Makeable;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $model, mixed $ignore = null, ?string $column = 'NULL')
    {
        parent::__construct(
            app($model)->getTable(),
            $column
        );

        if (! is_null($ignore)) {
            $ignoredId = is_int($ignore) ? $ignore : (request()->route($ignore) ?: null);

            $this->ignore($ignoredId);
        }
    }
}
