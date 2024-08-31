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

use Illuminate\Support\Str;
use Turahe\Core\Contracts\Countable;
use Illuminate\Database\Eloquent\Builder;

class BelongsToManyColumn extends RelationshipColumn implements Countable
{
    /**
     * BelongsToMany column is not sortable by default
     */
    public bool $sortable = false;

    /**
     * Indicates whether on the relation count query be performed
     */
    public bool $count = false;

    /**
     * Data heading component
     */
    public string $component = 'table-data-option-column';

    /**
     * Set that the column should count the results instead of quering all the data
     */
    public function count(): static
    {
        $this->count = true;
        $this->attribute = $this->countKey();
        $this->useComponent('table-data-column');

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
