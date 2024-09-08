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

namespace Turahe\Core\Calendar\Google;

use Google\Service\Exception as GoogleServiceException;
use Turahe\Core\Calendar\Exceptions\UnauthorizedException;
use Turahe\Core\Contracts\OAuth\Calendarable;
use Turahe\Core\Facades\Google as Client;
use Turahe\Core\OAuth\AccessTokenProvider;

class GoogleCalendar implements Calendarable
{
    /**
     * Initialize new GoogleCalendar instance.
     */
    public function __construct(protected AccessTokenProvider $token)
    {
        Client::connectUsing($token->getEmail());
    }

    /**
     * Get the available calendars.
     *
     * @return \Turahe\Core\Contracts\Calendar\Calendar[]
     */
    public function getCalendars()
    {
        try {
            return collect(Client::calendar()->list())
                ->mapInto(Calendar::class)
                ->all();
        } catch (GoogleServiceException $e) {
            $message = $e->getErrors()[0]['message'] ?? $e->getMessage();

            if ($e->getCode() == 403) {
                throw new UnauthorizedException($message, $e->getCode(), $e);
            }

            throw $e;
        }
    }
}
