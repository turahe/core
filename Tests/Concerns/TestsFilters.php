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

namespace Turahe\Core\Tests\Concerns;

use Tests\Fixtures\Event;
use Illuminate\Support\Facades\Request;
use Turahe\Core\Criteria\FilterRulesCriteria;

trait TestsFilters
{
    /**
     * Perform filers search
     *
     * @param  Criteria  $criteria
     * @return \Illuminate\Support\Collection
     */
    protected function perform($attribute, $operand, $value = null)
    {
        $filter = app(static::$filter, ['field' => $attribute]);

        $rule = $this->payload(
            $attribute,
            $value,
            $filter->type(),
            $operand
        );

        return (new Event())
            ->newQuery()
            ->criteria(
                new FilterRulesCriteria($rule, collect([$filter]), Request::instance())
            )->get();
    }

    /**
     * Get filter payload
     *
     * @param  string  $field
     * @param  mixed  $value
     * @param  string  $type
     * @param  string  $operator
     * @return array
     */
    protected function payload($field, $value, $type, $operator)
    {
        $rule = [
            'type'  => 'rule',
            'query' => [
                'type'     => $type,
                'rule'     => $field,
                'operator' => $operator,
                'value'    => $value,
            ],
        ];

        return [
            'condition' => 'and',
            'children'  => [$rule],
        ];
    }
}
