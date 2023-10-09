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

namespace Modules\Core\Filters;

use Modules\Core\Facades\Innoclapps;
use Modules\Core\QueryBuilder\Parser;

class HasMany extends OperandFilter
{
    /**
     * Apply the filter when custom query callback is provided
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  mixed  $value
     * @param  string  $condition
     * @param  array  $sqlOperator
     * @param  \stdClass  $rule
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($builder, $value, $condition, $sqlOperator, $rule, Parser $parser)
    {
        if ($parser->ruleCountsRelation($rule->operand->rule)) {
            return $parser->makeQueryWhenCountableRelation(
                $builder,
                $rule->operand->rule,
                $rule,
                $sqlOperator['operator'],
                $value,
                $condition,
                function ($builder) {
                    return $this->applyViewAuthorizedCriteriaIfNeeded($builder);
                }
            );
        }

        return $builder->has($this->field(), '>=', 1, $condition, function ($builder) use ($rule, $parser) {
            $this->applyViewAuthorizedCriteriaIfNeeded($builder);

            // Use AND for the subquery of the relation rules
            return $parser->makeQuery($builder, $rule, 'AND');
        });
    }

    /**
     * Apply view authorized criteria to the builder if the builder model
     * is associated with resources e.q. in has or whereHas
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    protected function applyViewAuthorizedCriteriaIfNeeded($query)
    {
        if ($criteria = Innoclapps::resourceByModel($query->getModel())?->viewAuthorizedRecordsCriteria()) {
            (new $criteria)->apply($query);
        }
    }

    /**
     * Check whether the filter has custom callback
     */
    public function hasCustomQuery(): bool
    {
        return true;
    }
}
