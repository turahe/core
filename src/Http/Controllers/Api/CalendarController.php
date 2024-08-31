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

namespace Turahe\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Contracts\DisplaysOnCalendar;
use Turahe\Core\Http\Controllers\ApiController;
use Illuminate\Contracts\Database\Query\Expression;
use Turahe\Core\Http\Resources\CalendarEventResource;

class CalendarController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $events = collect([]);

        foreach ($this->filterResourcesForCalendar($request) as $resource) {
            $query = $resource->newQuery();

            $events = $events->merge($this->applyQuery($query, $resource, $request)->get());
        }

        return $this->response(
            CalendarEventResource::collection($events)
        );
    }

    /**
     * Apply the calendar query
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Turahe\Core\Resource\Resource  $resource
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyQuery($query, $resource, $request)
    {
        $startColumn = $resource::$model::getCalendarStartColumnName();
        $endColumn = $resource::$model::getCalendarEndColumnName();

        $grammar = $query->getModel()->getConnection()->getQueryGrammar();

        $startColumnString = $startColumn instanceof Expression ? $startColumn->getValue($grammar) : $startColumn;
        $endColumnString = $endColumn instanceof Expression ? $endColumn->getValue($grammar) : $endColumn;

        $query = $query->whereRaw('? IS NOT NULL', $startColumn)
            ->whereRaw('? IS NOT NULL', $endColumn)
            ->where(function ($query) use ($startColumn, $startColumnString, $endColumnString, $endColumn, $request) {
                $query->where(function ($query) use ($startColumn, $startColumnString, $endColumnString, $request) {
                    // https://stackoverflow.com/questions/17014066/mysql-query-to-select-events-between-start-end-date
                    $spanRaw = "? between $startColumnString AND $endColumnString";

                    return $query->whereBetween($startColumn, [
                        $request->start_date,
                        $request->end_date,
                    ])->orWhereRaw($spanRaw, $request->start_date);
                });

                if (method_exists($query->getModel(), 'tapCalendarDateQuery')) {
                    $query->getModel()->tapCalendarDateQuery($query, $startColumn, $endColumn, $request);
                }
            });

        if (method_exists($query->getModel(), 'tapCalendarQuery')) {
            $query->getModel()->tapCalendarQuery($query, $request);
        }

        return $query;
    }

    /**
     * Filter the calendarable resources
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection
     */
    protected function filterResourcesForCalendar($request)
    {
        return Innoclapps::registeredResources()->filter(function ($resource) use ($request) {
            if (is_subclass_of($resource::$model, DisplaysOnCalendar::class)) {
                return $request->resource_name ? $resource->name() === $request->resource_name : true;
            }
        });
    }
}
