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

namespace Modules\Core\Criteria;

use Exception;
use Illuminate\Http\Request;
use Modules\Core\Date\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Contracts\Criteria\QueryCriteria;
use Modules\Core\ProvidesBetweenArgumentsViaString;

class ExportRequestCriteria implements QueryCriteria
{
    use ProvidesBetweenArgumentsViaString;

    /**
     * Create new ExportRequestCriteria instance.
     */
    public function __construct(protected Request $request)
    {
    }

    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $query): void
    {
        $model = $query->getModel();

        if (! $query->getModel()->usesTimestamps()) {
            throw new Exception('Exportable resource model must uses timestamps.');
        }

        $createdAtColumn = $model->getCreatedAtColumn();

        if ($period = $this->request->input('period')) {
            $betweenArgument = is_array($period) ?
                array_map(fn ($date) => Carbon::fromCurrentToAppTimezone($date), $period) :
                $this->getBetweenArguments($period);

            $query->whereBetween($createdAtColumn, $betweenArgument);
        }

        $query->orderByDesc($createdAtColumn);
    }
}
