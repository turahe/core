<?php

declare(strict_types=1);
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

namespace Turahe\Core\Google\Services;

use Google\Client;
use Google\Service\Calendar as GoogleCalendarService;
use Google\Service\Calendar\CalendarListEntry;

class Calendar extends Service
{
    /**
     * Initialize new Calendar service instance.
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, GoogleCalendarService::class);
    }

    /**
     * List all available user calendars
     *
     * @return CalendarListEntry[]
     */
    public function list()
    {
        /** @var Google\Service\Calendar */
        $service = $this->service;

        $calendars = [];
        $nextPage = null;

        do {
            $calendarList = $service->calendarList->listCalendarList([
                'pageToken' => $nextPage,
            ]);

            foreach ($calendarList->getItems() as $calendar) {
                $calendars[] = $calendar;
            }
        } while (($nextPage = $calendarList->getNextPageToken()));

        return $calendars;
    }

    /**
     * Get calendar by id
     *
     * @param  string  $id
     * @return CalendarListEntry
     */
    public function get($id)
    {
        /** @var Google\Service\Calendar */
        $service = $this->service;

        return $service->calendars->get('me', $id);
    }
}
