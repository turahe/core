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
use Illuminate\Database\Eloquent\Builder;

class BelongsToColumn extends RelationshipColumn
{
    /**
     * Apply the order by query for the column
     */
    public function orderBy(Builder $query, array $order): Builder
    {
        $relation = $this->relationName;
        $instance = $query->getModel()->{$relation}();
        $table = $instance->getModel()->getTable();

        $alias = Str::snake(class_basename($query->getModel())).'_'.$relation.'_'.$table;

        $query->leftJoin(
            $table.' as '.$alias,
            fn ($join) => $join->on($instance->getQualifiedForeignKeyName(), '=', $alias.'.id')
        );

        ['attribute' => $attribute, 'direction' => $direction] = $order;

        if (is_callable($this->orderByUsing)) {
            return call_user_func_array($this->orderByUsing, [$query, $attribute, $direction, $alias, $this]);
        }

        return $query->orderBy(
            $alias.'.'.$this->relationField,
            $direction
        );
    }
}
