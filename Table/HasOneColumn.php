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

use Illuminate\Database\Eloquent\Builder;

class HasOneColumn extends RelationshipColumn
{
    /**
     * Apply the order by query for the column
     */
    public function orderBy(Builder $query, array $order): Builder
    {
        $relation = $this->relationName;
        $relationTable = $query->getModel()->{$relation}()->getModel()->getTable();

        $query->leftJoin($relationTable, function ($join) use ($query, $relation) {
            $join->on(
                $query->getModel()->getQualifiedKeyName(),
                '=',
                $query->getModel()->{$relation}()->getQualifiedForeignKeyName()
            );
        });

        ['attribute' => $attribute, 'direction' => $direction] = $order;

        if (is_callable($this->orderByUsing)) {
            return call_user_func_array($this->orderByUsing, [$query, $attribute, $direction, $this]);
        }

        return $query->orderBy($this->relationField, $direction);
    }
}
