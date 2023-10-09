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

use Illuminate\Support\Str;
use Modules\Core\Contracts\Countable;
use Illuminate\Database\Eloquent\Builder;

class HasManyColumn extends RelationshipColumn implements Countable
{
    /**
     * HasMany columns are not by default sortable
     */
    public bool $sortable = false;

    /**
     * Indicates whether on the relation count query be performed
     */
    public bool $count = false;

    /**
     * Set that the column should count the results instead of quering all the data
     */
    public function count(): static
    {
        $this->count = true;
        $this->attribute = $this->countKey();

        return $this;
    }

    /**
     * Check whether a column query counts the relation
     */
    public function counts(): bool
    {
        return $this->count === true;
    }

    /**
     * Get the count key
     */
    public function countKey(): string
    {
        return Str::snake($this->attribute.'_count');
    }

    /**
     * Apply the order by query for the column
     */
    public function orderBy(Builder $query, array $order): Builder
    {
        if (! $this->counts()) {
            return $query;
        }

        return $query->orderBy($this->attribute, $order['direction']);
    }
}
