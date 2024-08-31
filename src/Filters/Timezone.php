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

namespace Turahe\Core\Filters;

use Turahe\Core\Fields\HasOptions;
use Turahe\Core\Fields\ChangesKeys;
use Turahe\Core\Facades\Timezone as Facade;

class Timezone extends Filter
{
    use ChangesKeys,
        HasOptions;

    /**
     * @param  string  $field
     * @param  string|null  $label
     * @param  null|array  $operators
     */
    public function __construct($field, $label = null, $operators = null)
    {
        parent::__construct($field, $label, $operators);

        $this->options(collect(Facade::toArray())->mapWithKeys(function ($timezone) {
            return [$timezone => $timezone];
        })->all());
    }

    /**
     * Defines a filter type
     */
    public function type(): string
    {
        return 'select';
    }
}
