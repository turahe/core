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

class RelatedCriteria implements QueryCriteria
{
    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $base): void
    {
        $base->where(function ($query) use ($base) {
            $resource = $base->getModel()->resource();

            foreach ($resource->availableAssociations() as $key => $resource) {
                if ($criteria = $resource->viewAuthorizedRecordsCriteria()) {
                    $relation = $resource->associateableName();

                    $query->{$key === 0 ? 'whereHas' : 'orWhereHas'}($relation, function ($query) use ($criteria) {
                        (new $criteria)->apply($query);
                    });
                }
            }

            if (method_exists($base, 'user')) {
                $query->orWhere($base->user()->getForeignKeyName(), auth()->id());
            }
        });
    }
}
