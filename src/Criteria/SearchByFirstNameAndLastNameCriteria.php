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

namespace Turahe\Core\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Turahe\Core\Contracts\Criteria\QueryCriteria;

class SearchByFirstNameAndLastNameCriteria implements QueryCriteria
{
    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $query)
    {
        $search = request('q');

        if ($raw = $query->getModel()->nameQueryExpression()) {
            $query->where($raw, 'LIKE', "%$search%");
        }
    }
}
