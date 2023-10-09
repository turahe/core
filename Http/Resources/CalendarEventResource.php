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

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Core\Contracts\DisplaysOnCalendar */
class CalendarEventResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->getKey(),
            'title'      => $this->getCalendarTitle($request->viewName),
            'start'      => $this->getCalendarStartDate($request->viewName),
            'end'        => $this->getCalendarEndDate($request->viewName),
            'allDay'     => $this->isAllDay(),
            'isReadOnly' => $request->user()->cant('update', $this->resource),
            'textColor'  => method_exists($this->resource, 'getCalendarEventTextColor') ?
                $this->getCalendarEventTextColor() :
                null,
            'backgroundColor' => $bgColor = method_exists($this->resource, 'getCalendarEventBgColor') ?
                $this->getCalendarEventBgColor() :
                '',
            'borderColor' => method_exists($this->resource, 'getCalendarEventBorderColor') ?
                $this->getCalendarEventBorderColor() :
                $bgColor,
            'extendedProps' => [
                'event_type' => strtolower(class_basename($this->resource)),
            ],
        ];
    }
}
