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

use Akaunting\Money\Currency;
use Modules\Core\Facades\Innoclapps;

class NumericColumn extends Column
{
    /**
     * Indicates whether the field has currency
     */
    public ?Currency $currency = null;

    /**
     * Initialize new NumericColumn instance.
     */
    public function __construct(...$params)
    {
        parent::__construct(...$params);

        // Do not use queryAs as it's not supported (tested) for this type of column
        $this->displayAs(function ($model) {
            $value = $model->{$this->attribute};

            if (empty($value)) {
                return '--';
            }

            if (! $this->currency) {
                return $value;
            }

            return $this->currency->toMoney($value)->format();
        });
    }

    /**
     * Set that the value should be displayed with currency
     */
    public function currency(string|Currency|null $currency = null): static
    {
        $this->currency = Innoclapps::currency($currency);

        return $this;
    }
}
