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

namespace Turahe\Core\Concerns;

use Illuminate\Database\Eloquent\Builder;

/** @mixin \Turahe\Core\Models\Model */
trait HasDisplayOrder
{
    /**
     * Boot the HasDisplayOrder trait.
     */
    protected static function bootHasDisplayOrder()
    {
        static::addGlobalScope('displayOrder', fn (Builder $query) => $query->orderByDisplayOrder());
    }

    /**
     * Scope a query to order the model by "display_order" column.
     */
    public function scopeOrderByDisplayOrder(Builder $query): void
    {
        $query->orderBy('display_order');
    }
}
